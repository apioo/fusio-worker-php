<?php

namespace Fusio\Worker;

use Fusio\Worker\Runtime\Runtime;
use RuntimeException;

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
        $name = $action;
        $hash = null;

        $pos = strpos($action, '@');
        if ($pos !== false) {
            $name = substr($action, 0, $pos);
            $hash = substr($action, $pos + 1);
        }

        $this->assertAction($name);
        if (!empty($hash)) {
            $this->assertHash($hash);
        }

        $baseDir = self::ACTIONS_DIR . '/' . $name;
        if (!is_dir($baseDir)) {
            mkdir($baseDir, recursive: true);
        }

        $fileName = "main";
        if (!empty($hash)) {
            $fileName = $hash;
        }

        return $baseDir . '/' . $fileName . '.php';
    }

    private function assertAction(string $action): void
    {
        if (!preg_match('/^[A-Za-z0-9_-]{3,255}$/', $action)) {
            throw new RuntimeException('Provided no valid action name');
        }
    }

    private function assertHash(?string $hash): void
    {
        if ($hash === null || !preg_match('/^[A-Za-z0-9]{3,255}$/', $hash)) {
            throw new RuntimeException('Provided no valid action hash');
        }
    }

    private function newMessage(bool $success, string $message): Message
    {
        $return = new Message();
        $return->setSuccess($success);
        $return->setMessage($message);

        return $return;
    }
}
