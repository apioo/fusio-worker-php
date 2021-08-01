<?php

namespace Fusio\Worker;

use Fusio\Worker\Generated\Log;

class Logger
{
    /**
     * @var Log[]
     */
    private array $logs = [];

    public function emergency(string $message)
    {
        $this->log('EMERGENCY', $message);
    }

    public function alert(string $message)
    {
        $this->log('ALERT', $message);
    }

    public function critical(string $message)
    {
        $this->log('CRITICAL', $message);
    }

    public function error(string $message)
    {
        $this->log('ERROR', $message);
    }

    public function warning(string $message)
    {
        $this->log('WARNING', $message);
    }

    public function notice(string $message)
    {
        $this->log('NOTICE', $message);
    }

    public function info(string $message)
    {
        $this->log('INFO', $message);
    }

    public function debug(string $message)
    {
        $this->log('DEBUG', $message);
    }

    private function log(string $level, string $message)
    {
        $this->logs[] = new Log([
            'level' => $level,
            'message' => $message
        ]);
    }

    public function getLogs(): array
    {
        return $this->logs;
    }
}
