<?php

namespace Fusio\Worker;

use Fusio\Worker\Generated\Log;

class Logger
{
    /**
     * @var Log[]
     */
    private array $logs = [];

    public function emergency(string $message): void
    {
        $this->log('EMERGENCY', $message);
    }

    public function alert(string $message): void
    {
        $this->log('ALERT', $message);
    }

    public function critical(string $message): void
    {
        $this->log('CRITICAL', $message);
    }

    public function error(string $message): void
    {
        $this->log('ERROR', $message);
    }

    public function warning(string $message): void
    {
        $this->log('WARNING', $message);
    }

    public function notice(string $message): void
    {
        $this->log('NOTICE', $message);
    }

    public function info(string $message): void
    {
        $this->log('INFO', $message);
    }

    public function debug(string $message): void
    {
        $this->log('DEBUG', $message);
    }

    private function log(string $level, string $message): void
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
