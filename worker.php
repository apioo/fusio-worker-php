<?php

require __DIR__ . '/vendor/autoload.php';

$logger = new \Monolog\Logger('Worker', [new \Monolog\Handler\StreamHandler(STDOUT)]);
$handler = new \Fusio\Worker\WorkerHandler($logger);
$server = new \Fusio\Worker\Thrift\Server(
    new \Fusio\Worker\Generated\WorkerProcessor($handler),
    9092,
    new \Fusio\Worker\Thrift\Factory(),
    $logger
);
$server->serve();
