<?php

namespace Fusio\Worker\Thrift;

use Amp\Loop;
use Amp\Socket\ResourceSocket;
use Amp\Socket\Server as AmpServer;
use Fusio\Worker\Generated\WorkerProcessor;
use Psr\Log\LoggerInterface;
use Thrift\Factory\TProtocolFactory;
use function Amp\asyncCoroutine;

class Server
{
    private WorkerProcessor $processor;
    private int $port;
    private TProtocolFactory $protocolFactory;
    private LoggerInterface $logger;

    public function __construct(WorkerProcessor $processor, int $port, TProtocolFactory $protocolFactory, LoggerInterface $logger)
    {
        $this->processor = $processor;
        $this->port = $port;
        $this->protocolFactory = $protocolFactory;
        $this->logger = $logger;
    }

    public function serve()
    {
        Loop::set(new Loop\UvDriver());
        Loop::run(function () {
            $clientHandler = asyncCoroutine(function (ResourceSocket $socket) {
                $transport = new Transport($socket);

                $input = $this->protocolFactory->getProtocol($transport);
                $output = $this->protocolFactory->getProtocol($transport);

                $this->processor->process($input, $output);
            });

            $this->logger->info('Fusio Worker started');

            $server = AmpServer::listen('0.0.0.0:' . $this->port);

            while ($socket = yield $server->accept()) {
                $clientHandler($socket);
            }
        });
    }

    public function stop()
    {
    }
}
