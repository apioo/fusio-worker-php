<?php

namespace Fusio\Worker;

use PSX\Record\Record;

class ResponseBuilder
{
    public function build(int $statusCode, array $headers, mixed $body): ResponseHTTP
    {
        $return = new ResponseHTTP();
        $return->setStatusCode($statusCode);
        $return->setHeaders(Record::fromArray($headers));
        $return->setBody($body);

        return $return;
    }
}
