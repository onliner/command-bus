<?php

declare(strict_types=1);

use Onliner\CommandBus\Builder;
use Onliner\CommandBus\Remote\AMQP\AMQPTransport;
use Onliner\CommandBus\Remote\AMQP\AMQPConsumer;

/** @var Builder $builder */
$builder = require __DIR__ . '/builder.php';
$builder->handle(Bar\Hello::class, function (Bar\Hello $command) {
    echo sprintf('Hello %s from bar!', $command->name), PHP_EOL;
});

$dispatcher = $builder->build();

$transport = AMQPTransport::create('amqp://guest:guest@localhost:5673', [
    'exchange' => 'bar',
]);

/** @var AMQPConsumer $consumer */
$consumer = $transport->consume();
$consumer->listen('#');
$consumer->run([$dispatcher, 'dispatch']);
