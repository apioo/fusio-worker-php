<?php

namespace Fusio\Worker;

class Dispatcher
{
    /**
     * @var ResponseEvent[]
     */
    private array $events = [];

    public function dispatch(string $eventName, mixed $data): void
    {
        $event = new ResponseEvent();
        $event->setEventName($eventName);
        $event->setData($data);

        $this->events[] = $event;
    }

    public function getEvents(): array
    {
        return $this->events;
    }
}
