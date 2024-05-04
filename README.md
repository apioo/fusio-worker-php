
# Fusio-Worker-PHP

A Fusio worker implementation to execute PHP code.
More information about the Worker system at our documentation:
https://docs.fusio-project.org/docs/use_cases/api_gateway/worker

## Example

The following example shows an action written in PHP which gets data
from a database and returns a response

```php
<?php

use Fusio\Worker\Connector;
use Fusio\Worker\Dispatcher;
use Fusio\Worker\ExecuteContext;
use Fusio\Worker\ExecuteRequest;
use Fusio\Worker\Logger;
use Fusio\Worker\ResponseBuilder;

return function(ExecuteRequest $request, ExecuteContext $context, Connector $connector, ResponseBuilder $response, Dispatcher $dispatcher, Logger $logger) {
    $connection = $connector->getConnection('app');

    $query = 'SELECT name, description FROM app_product_0';
    $entries = $connection->fetchAllAssociative($query);

    return $response->build(200, [], [
        'foo' => 'bar',
        'entries' => $entries,
    ]);
};

```

## Types

This table contains an overview which connection types are implemented
and which implementation is used:

| Type                                                   | Implementation                |
|--------------------------------------------------------|-------------------------------|
| `Fusio.Adapter.Sql.Connection.Sql`                     | `doctrine/dbal`               |
| `Fusio.Adapter.Sql.Connection.SqlAdvanced`             | `doctrine/dbal`               |
| `Fusio.Adapter.Http.Connection.Http`                   | `guzzlehttp/guzzle`           |
| `Fusio.Adapter.Mongodb.Connection.MongoDB`             | `mongodb/mongodb`             |
| `Fusio.Adapter.Elasticsearch.Connection.Elasticsearch` | `elasticsearch/elasticsearch` |
