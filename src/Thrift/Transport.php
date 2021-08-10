<?php

namespace Fusio\Worker\Thrift;

use Amp\Socket\ResourceSocket;
use Thrift\Transport\TTransport;

class Transport extends TTransport
{
    private ResourceSocket $socket;

    public function __construct(ResourceSocket $socket)
    {
        $this->socket = $socket;
    }

    public function isOpen()
    {
        return !$this->socket->isClosed();
    }

    public function open()
    {
    }

    public function close()
    {
        $this->socket->close();
    }

    public function read($len)
    {
        return @\stream_get_contents($this->socket->getResource(), $len);
    }

    public function write($buf)
    {
        $this->socket->write($buf);
    }

    public function flush()
    {
    }
}
