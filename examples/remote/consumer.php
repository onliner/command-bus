<?php

declare(strict_types=1);

use Onliner\CommandBus\Remote\Bunny\BunnyConsumer;
use Onliner\CommandBus\Remote\Bunny\BunnyTransport;
use Onliner\CommandBus\Remote\Bunny\ExchangeOptions;

$dispatcher = require __DIR__ . '/dispatcher.php';

$transport = BunnyTransport::create('amqp://guest:guest@localhost:5672', new ExchangeOptions('mailer'));

/** @var BunnyConsumer $consumer */
$consumer = $transport->consume();
$consumer->bind( '#');
$consumer->start($dispatcher);
