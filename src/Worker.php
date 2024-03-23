<?php

namespace Fusio\Worker;

use PSX\Schema\SchemaManager;
use PSX\Schema\SchemaTraverser;
use PSX\Schema\Visitor\TypeVisitor;

class Worker
{
    private const ACTIONS_DIR = __DIR__ . '/../actions';

    public function get(): About
    {
        $about = new About();
        $about->setApiVersion('1.0.0');
        $about->setLanguage('php');

        return $about;
    }

    public function execute(string $action, \stdClass $payload): Response
    {
        $execute = $this->parseExecute($payload);
        $connector = new Connector($execute->getConnections());
        $dispatcher = new Dispatcher();
        $logger = new Logger();
        $responseBuilder = new ResponseBuilder();

        $file = $this->getActionFile($action);

        $handler = require $file;
        if (!is_callable($handler)) {
            throw new \RuntimeException('Provided action does not return a callable');
        }

        $response = call_user_func_array($handler, [
            $execute->getRequest(),
            $execute->getContext(),
            $connector,
            $responseBuilder,
            $dispatcher,
            $logger
        ]);

        if (!$response instanceof ResponseHTTP) {
            $response = new ResponseHTTP();
            $response->setStatusCode(204);
        }

        $return = new Response();
        $return->setEvents($dispatcher->getEvents());
        $return->setLogs($logger->getLogs());
        $return->setResponse($response);

        return $return;
    }

    public function put(string $action, \stdClass $payload): Message
    {
        if (!is_dir(self::ACTIONS_DIR)) {
            mkdir(self::ACTIONS_DIR);
        }

        $code = $payload->code ?? '';

        file_put_contents($this->getActionFile($action), $code);

        return $this->newMessage(true, 'Action successfully updated');
    }

    public function delete(string $action): Message
    {
        if (!is_dir(self::ACTIONS_DIR)) {
            mkdir(self::ACTIONS_DIR);
        }

        unlink($this->getActionFile($action));

        return $this->newMessage(true, 'Action successfully deleted');
    }

    private function getActionFile(string $action): string
    {
        if (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $action)) {
            throw new \RuntimeException('Provided no valid action name');
        }

        return self::ACTIONS_DIR . '/' . $action . '.php';
    }

    private function newMessage(bool $success, string $message): Message
    {
        $return = new Message();
        $return->setSuccess($success);
        $return->setMessage($message);

        return $return;
    }

    private function parseExecute(\stdClass $payload): Execute
    {
        $schema = (new SchemaManager())->getSchema(Execute::class);
        $execute = (new SchemaTraverser())->traverse($payload, $schema, new TypeVisitor());

        if (!$execute instanceof Execute) {
            throw new \RuntimeException('Could not read execute payload');
        }

        return $execute;
    }
}
