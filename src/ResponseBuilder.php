<?php

namespace Fusio\Worker;

use PSX\Record\Record;

class ResponseBuilder
{
    private ?ResponseHTTP $response = null;

    public function build(int $statusCode, array $headers, mixed $body): void
    {
        $this->response = new ResponseHTTP();
        $this->response->setStatusCode($statusCode);
        $this->response->setHeaders(Record::fromArray($headers));
        $this->response->setBody($body);
    }

    public function getResponse(): ?ResponseHTTP
    {
        return $this->response;
    }
}
