<?php

declare(strict_types=1);

use Onliner\CommandBus\Remote\AMQP\AMQPTransport;
use Onliner\CommandBus\Remote\AMQP\AMQPConsumer;

$dispatcher = require __DIR__ . '/dispatcher.php';
$transport = AMQPTransport::create('amqp://guest:guest@localhost:5672', [
    'exchange' => 'mailer',
]);

/** @var AMQPConsumer $consumer */
$consumer = $transport->consume();
$consumer->listen('#');

pcntl_async_signals(true);

foreach ([SIGINT, SIGTERM] as $signal) {
    pcntl_signal($signal, function (int $signo) use ($consumer) {
        $consumer->stop();

        echo sprintf('Received signal: %d', $signo), \PHP_EOL;
    });
}

$consumer->run($dispatcher, [
    AMQPConsumer::OPTION_ATTEMPTS => 10,
    AMQPConsumer::OPTION_INTERVAL => 100000, // 100 ms
]);
