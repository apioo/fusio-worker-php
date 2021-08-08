<?php

require __DIR__ . '/vendor/autoload.php';

header('Content-Type', 'application/x-thrift');

$port = 9092;

$handler = new \Fusio\Worker\WorkerHandler();
$processor = new \Fusio\Worker\Generated\WorkerProcessor($handler);

$socket = new \Thrift\Server\TServerSocket('0.0.0.0', $port);
$transportFactory = new \Thrift\Factory\TTransportFactory();
$protocolFactory = new \Thrift\Factory\TBinaryProtocolFactory();

echo 'Fusio Worker started' . "\n";

$server = new \Thrift\Server\TSimpleServer($processor, $socket, $transportFactory, $transportFactory, $protocolFactory, $protocolFactory);
$server->serve();

