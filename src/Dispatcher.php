<?php

namespace Fusio\Worker;

use Fusio\Worker\Generated\Event;

class Dispatcher
{
    /**
     * @var Event[]
     */
    private array $events = [];

    public function dispatch(string $eventName, mixed $data): void
    {
        $this->events[] = new Event([
            'eventName' => $eventName,
            'data' => json_encode($data),
        ]);
    }

    public function getEvents(): array
    {
        return $this->events;
    }
}
