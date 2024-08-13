<?php

declare(strict_types=1);

use Onliner\CommandBus\Builder;
use Onliner\CommandBus\Remote\AMQP\Transport;
use Onliner\CommandBus\Remote\AMQP\Consumer;

/** @var Builder $builder */
$builder = require __DIR__ . '/builder.php';
$builder->handle(Foo\Hello::class, function (Foo\Hello $command) {
    echo sprintf('Hello %s from foo!', $command->name), PHP_EOL;
});

$transport = Transport::create('amqp://guest:guest@localhost:5672');
$consumer = $transport->consume();
$consumer->listen('#', 'foo');
$consumer->run($builder->build());
