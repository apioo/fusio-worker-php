<?php

namespace Fusio\Worker;

use Fusio\Worker\Generated\Response;

class ResponseBuilder
{
    public function build(int $statusCode, array $headers, $body): Response
    {
        return new Response([
            'statusCode' => $statusCode,
            'headers' => $headers,
            'body' => json_encode($body)
        ]);
    }
}
