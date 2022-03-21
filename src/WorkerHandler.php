<?php

namespace Fusio\Worker;

use Fusio\Worker\Generated\Message;
use Fusio\Worker\Generated\Response;
use Fusio\Worker\Generated\Result;
use Fusio\Worker\Generated\WorkerIf;
use Psr\Log\LoggerInterface;

class WorkerHandler implements WorkerIf
{
    private const ACTIONS_DIR = __DIR__.'./actions';
    private ?\stdClass $connections = null;
    private array $actions = [];
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function setConnection($connection)
    {
        if (!is_dir(self::ACTIONS_DIR)) {
            mkdir(self::ACTIONS_DIR);
        }

        $file = self::ACTIONS_DIR . '/connections.json';
        $data = $this->readConnections();

        if (empty($connection->name)) {
            return new Message(['success' => false, 'message' => 'Provided no connection name']);
        }

        $data->{$connection->name} = [
            'type' => $connection->type,
            'config' => $connection->config,
        ];

        file_put_contents($file, json_encode($data));

        // reset connections
        $this->connections = null;

        $this->logger->info('Update connection ' . $connection->name);

        return new Message(['success' => true, 'message' => 'Connection successful updated']);
    }

    /**
     * @inheritDoc
     */
    public function setAction($action)
    {
        if (!is_dir(self::ACTIONS_DIR)) {
            mkdir(self::ACTIONS_DIR);
        }

        if (empty($action->name)) {
            return new Message(['success' => false, 'message' => 'Provided no action name']);
        }

        $file = self::ACTIONS_DIR . '/' . $action->name . '.php';
        file_put_contents($file, $action->code);

        if (isset($this->actions[$action->name])) {
            unset($this->actions[$action->name]);
        }

        clearstatcache();

        $this->logger->info('Update action ' . $action->name);

        return new Message(['success' => true, 'message' => 'Action successful updated']);
    }

    /**
     * @inheritDoc
     */
    public function executeAction($execute)
    {
        $connector = new Connector($this->readConnections());
        $dispatcher = new Dispatcher();
        $logger = new Logger();
        $responseBuilder = new ResponseBuilder();

        $this->logger->error('Execute action ' . $execute->action);

        try {
            if (!isset($this->actions[$execute->action])) {
                $file = self::ACTIONS_DIR . '/' . $execute->action . '.php';
                if (!is_file($file)) {
                    throw new \RuntimeException('Provided action does not exist');
                }

                $handler = require $file;
                if (!is_callable($handler)) {
                    throw new \RuntimeException('Provided action does not return a callable');
                }

                $this->actions[$execute->action] = $handler;
            }

            $response = call_user_func_array($this->actions[$execute->action], [
                $execute->request,
                $execute->context,
                $connector,
                $responseBuilder,
                $dispatcher,
                $logger
            ]);

            return new Result([
                'response' => $response,
                'events' => $dispatcher->getEvents(),
                'logs' => $logger->getLogs()
            ]);
        } catch (\Throwable $e) {
            $response = new Response([
                'statusCode' => 500,
                'headers' => [],
                'body' => json_encode([
                    'success' => false,
                    'message' => 'An error occurred at the worker: ' . $e->getMessage(),
                ]),
            ]);

            $this->logger->error('An error occurred: ' . $e->getMessage());

            return new Result(['response' => $response]);
        }
    }

    private function readConnections(): \stdClass
    {
        if (!empty($this->connections)) {
            return $this->connections;
        }

        $file = self::ACTIONS_DIR . '/connections.json';
        if (is_file($file)) {
            $this->connections = json_decode(file_get_contents($file));
        }

        if (!$this->connections instanceof \stdClass) {
            return new \stdClass();
        }

        return $this->connections;
    }
}
