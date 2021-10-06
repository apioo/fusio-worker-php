<?php

require __DIR__ . '/../vendor/autoload.php';

header('Content-Type', 'application/x-thrift');

$logger = new \Monolog\Logger('Worker', [new \Monolog\Handler\ErrorLogHandler()]);
$handler = new \Fusio\Worker\WorkerHandler($logger);
$processor = new \Fusio\Worker\Generated\WorkerProcessor($handler);

$transport = new \Thrift\Transport\TBufferedTransport(new \Thrift\Transport\TPhpStream(\Thrift\Transport\TPhpStream::MODE_R | \Thrift\Transport\TPhpStream::MODE_W));
$protocol = new \Thrift\Protocol\TBinaryProtocol($transport, true, true);

$transport->open();
$processor->process($protocol, $protocol);
$transport->close();
