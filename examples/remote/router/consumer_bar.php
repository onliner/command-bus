<?php

declare(strict_types=1);

use Onliner\CommandBus\Builder;
use Onliner\CommandBus\Remote\AMQP\Exchange;
use Onliner\CommandBus\Remote\AMQP\Transport;
use Onliner\CommandBus\Remote\AMQP\Consumer;

/** @var Builder $builder */
$builder = require __DIR__ . '/builder.php';
$builder->handle(Bar\Hello::class, function (Bar\Hello $command) {
    echo sprintf('Hello %s from bar!', $command->name), PHP_EOL;
});

$dispatcher = $builder->build();
$transport = Transport::create('amqp://guest:guest@localhost:5672');
$transport->declare(Exchange::create(['name' => 'bar']));

/** @var Consumer $consumer */
$consumer = $transport->consume();
$consumer->listen('#', 'bar');
$consumer->run($dispatcher);
