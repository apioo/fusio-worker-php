<?php

require __DIR__ . '/../vendor/autoload.php';

header('Content-Type', 'application/x-thrift');

$handler = new \Fusio\Worker\WorkerHandler();
$processor = new \Fusio\Worker\Generated\WorkerProcessor($handler);

$transport = new \Thrift\Transport\TBufferedTransport(new \Thrift\Transport\TPhpStream(\Thrift\Transport\TPhpStream::MODE_R | \Thrift\Transport\TPhpStream::MODE_W));
$protocol = new \Thrift\Protocol\TBinaryProtocol($transport, true, true);

$transport->open();
$processor->process($protocol, $protocol);
$transport->close();
