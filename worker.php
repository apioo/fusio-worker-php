<?php

require __DIR__ . '/vendor/autoload.php';

$handler = new \Fusio\Worker\WorkerHandler();
$server = new \Fusio\Worker\Thrift\Server(
    new \Fusio\Worker\Generated\WorkerProcessor($handler),
    9092,
    new \Fusio\Worker\Thrift\Factory()
);
$server->serve();
