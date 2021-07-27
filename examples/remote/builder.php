<?php

declare(strict_types=1);

use Onliner\CommandBus\Builder;
use Onliner\CommandBus\Remote\AMQP\AMQPTransport;
use Onliner\CommandBus\Remote\RemoteExtension;

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/messages.php';

$transport = AMQPTransport::create('amqp://guest:guest@localhost:5672', [
    'exchange' => 'foo',
]);

return (new Builder())
    ->use(new RemoteExtension($transport))
;
