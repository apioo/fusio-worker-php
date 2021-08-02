<?php

namespace Fusio\Worker;

use Fusio\Worker\Generated\Message;
use Fusio\Worker\Generated\Response;
use Fusio\Worker\Generated\Result;
use Fusio\Worker\Generated\WorkerIf;

class WorkerHandler implements WorkerIf
{
    private const ACTIONS_DIR = './actions';
    private ?\stdClass $connections = null;

    /**
     * @inheritDoc
     */
    public function setConnection($connection)
    {
        $dir = self::ACTIONS_DIR;
        if (!is_dir($dir)) {
            mkdir($dir);
        }

        $file = $dir . '/connections.json';
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

        return new Message(['success' => true, 'message' => 'Connection successful updated']);
    }

    /**
     * @inheritDoc
     */
    public function setAction($action)
    {
        $dir = self::ACTIONS_DIR;
        if (!is_dir($dir)) {
            mkdir($dir);
        }

        if (empty($action->name)) {
            return new Message(['success' => false, 'message' => 'Provided no action name']);
        }

        $file = $dir . '/' . $action->name . '.php';
        file_put_contents($file, $action->code);

        clearstatcache();

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
        $response = new ResponseBuilder();

        try {
            $file = self::ACTIONS_DIR . '/' . $execute->action . '.php';
            if (!is_file($file)) {
                throw new \RuntimeException('Provided action does not exist');
            }

            $handler = require $file;
            if (!$handler instanceof \Closure) {
                throw new \RuntimeException('Provided action does not return a closure');
            }

            $response = $handler($execute->request, $execute->context, $connector, $response, $dispatcher, $logger);

            return new Result(['response' => $response, 'events' => $dispatcher->getEvents(), 'logs' => $logger->getLogs()]);
        } catch (\Throwable $e) {
            $response = new Response([
                'statusCode' => 500,
                'headers' => [],
                'body' => json_encode([
                    'success' => false,
                    'message' => 'An error occurred at the worker: ' . $e->getMessage(),
                ]),
            ]);

            return new Result(['response' => $response]);
        }
    }

    private function readConnections(): ?\stdClass
    {
        if (!empty($this->connections)) {
            return $this->connections;
        }

        $file = self::ACTIONS_DIR . '/connections.json';
        if (is_file($file)) {
            $this->connections = json_decode(file_get_contents($file));
        }

        if (!$this->connections instanceof \stdClass) {
            return null;
        }

        return $this->connections;
    }
}
