<?php

namespace Fusio\Worker;

class Connector
{
    private array $connections;

    public function __construct(array $connections)
    {
        $this->connections = $connections;
    }

    public function getConnection(string $name)
    {

    }
}
