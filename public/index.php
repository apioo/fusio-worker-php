<?php

use Fusio\Worker\Message;

require __DIR__ . '/../vendor/autoload.php';

\header('Content-Type', 'application/json');

$action = \ltrim($_SERVER['PATH_INFO'] ?? '', '/');
$worker = new \Fusio\Worker\Worker();

try {
    $code = 200;
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $response = $worker->get();
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $payload = \json_decode(file_get_contents('php://input'));
        $response = $worker->execute($action, $payload);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        $payload = \json_decode(file_get_contents('php://input'));
        $response = $worker->put($action, $payload);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $response = $worker->delete($action);
    } else {
        $code = 405;
        $response = newErrorMessage('HTTP method not allowed');
    }

    \http_response_code($code);
    echo \json_encode($response);
} catch (\Throwable $e) {
    \http_response_code(500);
    echo \json_encode(newErrorMessage($e->getMessage(), $e->getTraceAsString()));
}

function newErrorMessage(string $message, ?string $trace = null): Message
{
    $return = new Message();
    $return->setSuccess(false);
    $return->setMessage($message);
    $return->setTrace($trace);
    return $return;
}
