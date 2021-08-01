
# Fusio-Worker-PHP

A Fusio worker implementation to execute PHP code.
More information about the worker API at:
https://www.fusio-project.org/documentation/worker

## Example

The following example shows an action written in PHP which gets data
from a database and returns a response

```php
<?php

return function($request, $context, $connector, $response, $dispatcher, $logger) {
    $connection = $connector->getConnection('my_db');
    
    $result = $connection->fetchAllAssociative('SELECT * FROM app_todo');
    
    return $response->build(200, [], [
        'foo' => 'bar',
        'result' => $result,
    ]);
};

```
