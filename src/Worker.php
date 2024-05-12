<?php

namespace Fusio\Worker;

use Fusio\Worker\Runtime\Runtime;

class Worker
{
    private const ACTIONS_DIR = __DIR__ . '/../actions';

    private Runtime $runtime;

    public function __construct()
    {
        $this->runtime = new Runtime();
    }

    public function get(): About
    {
        return $this->runtime->get();
    }

    public function execute(string $action, \stdClass $payload): Response
    {
        return $this->runtime->run($this->getActionFile($action), $payload);
    }

    public function put(string $action, \stdClass $payload): Message
    {
        if (!is_dir(self::ACTIONS_DIR)) {
            mkdir(self::ACTIONS_DIR);
        }

        $file = $this->getActionFile($action);
        $code = $payload->code ?? '';

        file_put_contents($file, $code);

        return $this->newMessage(true, 'Action successfully updated');
    }

    public function delete(string $action): Message
    {
        if (!is_dir(self::ACTIONS_DIR)) {
            mkdir(self::ACTIONS_DIR);
        }

        $file = $this->getActionFile($action);

        if (is_file($file)) {
            unlink($file);
        }

        return $this->newMessage(true, 'Action successfully deleted');
    }

    private function getActionFile(string $action): string
    {
        if (!preg_match('/^[A-Za-z0-9_-]{3,30}$/', $action)) {
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
}
