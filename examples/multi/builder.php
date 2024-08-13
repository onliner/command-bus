<?php

declare(strict_types=1);

use Onliner\CommandBus\Builder;
use Onliner\CommandBus\Remote\AMQP\Transport;
use Onliner\CommandBus\Remote\RemoteExtension;
use Onliner\CommandBus\Remote\Transport;

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/messages.php';

$transportFoo = Transport::create('amqp://guest:guest@localhost:5672', 'foo');
$transportBar = Transport::create('amqp://guest:guest@localhost:5673', 'bar');

$transport = new Transport\MultiTransport($transportFoo);
$transport->add('Bar\*', $transportBar);

return (new Builder())
    ->use(new RemoteExtension($transport))
;
