<?php

declare(strict_types=1);

use Onliner\CommandBus\Builder;
use Onliner\CommandBus\Remote\AMQP\AMQPTransport;
use Onliner\CommandBus\Remote\AMQP\AMQPConsumer;

/** @var Builder $builder */
$builder = require __DIR__ . '/builder.php';
$builder->handle(Foo\Hello::class, function (Foo\Hello $command) {
    echo sprintf('Hello %s from foo!', $command->name), PHP_EOL;
});

$dispatcher = $builder->build();

$transport = AMQPTransport::create('amqp://guest:guest@localhost:5672', [
    'exchange' => 'foo',
]);

/** @var AMQPConsumer $consumer */
$consumer = $transport->consume();
$consumer->listen('#');
$consumer->run($dispatcher);
