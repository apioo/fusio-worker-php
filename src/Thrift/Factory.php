<?php

namespace Fusio\Worker\Thrift;

use Thrift\Factory\TProtocolFactory;
use Thrift\Protocol\TBinaryProtocol;

class Factory implements TProtocolFactory
{
    private bool $strictRead;
    private bool $strictWrite;

    public function __construct(bool $strictRead = false, bool $strictWrite = false)
    {
        $this->strictRead = $strictRead;
        $this->strictWrite = $strictWrite;
    }

    public function getProtocol($trans)
    {
        return new TBinaryProtocol($trans, $this->strictRead, $this->strictWrite);
    }
}
