
# Fusio-Worker-PHP

A Fusio worker implementation to execute PHP code.
More information about the worker API at:
https://www.fusio-project.org/documentation/worker

## Example

The following example shows an action written in PHP which gets data
from a database and returns a response

```php
<?php

use Fusio\Worker\Connector;
use Fusio\Worker\Dispatcher;
use Fusio\Worker\Generated\Context;
use Fusio\Worker\Generated\Request;
use Fusio\Worker\Logger;
use Fusio\Worker\ResponseBuilder;

return function(Request $request, Context $context, Connector $connector, ResponseBuilder $response, Dispatcher $dispatcher, Logger $logger) {
    $connection = $connector->getConnection('my_db');
    
    $result = $connection->fetchAllAssociative('SELECT * FROM app_todo');
    
    return $response->build(200, [], [
        'foo' => 'bar',
        'result' => $result,
    ]);
};

```

## Types

This table contains an overview which connection types are implemented
and which implementation is used:

| Type | Implementation |
| ---- | -------------- |
| `Fusio.Adapter.Sql.Connection.Sql` | `doctrine/dbal`
| `Fusio.Adapter.Sql.Connection.SqlAdvanced` | `doctrine/dbal`
| `Fusio.Adapter.Http.Connection.Http` | `guzzlehttp/guzzle`
| `Fusio.Adapter.Mongodb.Connection.MongoDB` | `-`
| `Fusio.Adapter.Elasticsearch.Connection.Elasticsearch` | `-`
