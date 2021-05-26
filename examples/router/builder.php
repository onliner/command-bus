<?php

declare(strict_types=1);

use Onliner\CommandBus\Builder;
use Onliner\CommandBus\Remote\AMQP\AMQPTransport;
use Onliner\CommandBus\Remote\RemoteExtension;
use Onliner\CommandBus\Remote\Router;

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/messages.php';

$transportFoo = AMQPTransport::create('amqp://guest:guest@localhost:5672', [
    'exchange' => 'foo',
]);
$transportBar = AMQPTransport::create('amqp://guest:guest@localhost:5673', [
    'exchange' => 'bar',
]);

$router = new Router($transportFoo);
$router->add('Bar\*', $transportBar);

return (new Builder())
    ->use(new RemoteExtension($router))
;
