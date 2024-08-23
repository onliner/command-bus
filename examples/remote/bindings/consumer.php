<?php

declare(strict_types=1);

use Onliner\CommandBus\Builder;
use Onliner\CommandBus\Context;
use Onliner\CommandBus\Remote\AMQP\Exchange;
use Onliner\CommandBus\Remote\AMQP\Flags;
use Onliner\CommandBus\Remote\AMQP\Packager;
use Onliner\CommandBus\Remote\AMQP\Transport;
use Onliner\CommandBus\Remote\AMQP\Consumer;
use Onliner\CommandBus\Remote\AMQP\Queue;

/** @var Builder $builder */
$builder = require __DIR__ . '/builder.php';
$builder->handle(SendEmail::class, function (SendEmail $command, Context $context) {
    $exchange = $context->get(Packager::OPTION_EXCHANGE);
    $routingKey = $context->get(Packager::OPTION_ROUTING_KEY);

    echo sprintf('Received message from %s with routing key %s', $exchange, $routingKey), PHP_EOL;

    // Throw exception to trigger DLE
    if ($exchange === 'foo') {
        throw new Exception("Something went wrong...");
    }
});

$transport = Transport::create('amqp://guest:guest@localhost:5672');
$transport->declare(Exchange::create(['name' => 'dle']));
$transport->declare(Exchange::create(['name' => 'foo']));

$consumer = $transport->consume();
$consumer->consume(new Queue('my-queue', [
    'foo' => '#',
    'dle' => 'sendemail',
], Flags::default(), args: [
    Queue::DEAD_LETTER => 'dle',
]));

$consumer->run($builder->build(), [
    Consumer::OPTION_ATTEMPTS => 10,
    Consumer::OPTION_INTERVAL => 100000, // 100 ms
]);
