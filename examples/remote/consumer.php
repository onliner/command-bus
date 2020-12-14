<?php

declare(strict_types=1);

use Onliner\CommandBus\Remote\Bunny\BunnyConsumer;
use Onliner\CommandBus\Remote\Bunny\BunnyTransport;

$dispatcher = require __DIR__ . '/dispatcher.php';
$transport = BunnyTransport::create('amqp://guest:guest@localhost:5672', [
    'exchange' => 'mailer',
]);

/** @var BunnyConsumer $consumer */
$consumer = $transport->consume();
$consumer->listen('#');

pcntl_async_signals(true);

foreach ([SIGINT, SIGTERM] as $signal) {
    pcntl_signal($signal, function (int $signo) use ($consumer) {
        $consumer->stop();

        echo sprintf('Received signal: %d', $signo), \PHP_EOL;
    });
}

$consumer->run($dispatcher);
