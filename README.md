
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

## Design

It is important to note that the worker is implemented as real server using `amphp/amp`
so we dont have a classical web server setup. This means that we dont have a
shared-nothing architecture and that you could run into memory-leaks if you store
data in a global context.

In general the worker requires the action once and then uses the returned closure
to serve all requests. If we update an action we require the action again to also
update the closure. But this means also if your action has previously defined a
function i.e. `doFoo` you will run into an `Cannot redeclare doFoo()` error.
So it is recommended to execute all actions inside the closure and without global
state and side effects.

On the other side this approach has a great performance since we only need to
require the action once and then use the closure to serve all requests.
Because of this setup we could write i.e. the following action, which simply
increases a count per request:

```php
<?php

$count = 0;

return function(Request $request, Context $context, Connector $connector, ResponseBuilder $response, Dispatcher $dispatcher, Logger $logger) use ($count) {

    $count++;

    return $response->build(200, [], [
        'count' => $count,
    ]);
};

```

