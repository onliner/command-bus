<?php

declare(strict_types=1);

use Onliner\CommandBus\Builder;
use Onliner\CommandBus\Remote\AMQP\Transport;
use Onliner\CommandBus\Remote\AMQP\Consumer;
use Onliner\CommandBus\Remote\AMQP\Queue;

/** @var Builder $builder */
$builder = require __DIR__ . '/builder.php';
$builder->handle(SendEmail::class, function (SendEmail $command) {
    echo 'MAILTO: ',  $command->to, \PHP_EOL;
    echo 'SUBJECT: ', $command->subject, \PHP_EOL;
    echo 'CONTENT: ', $command->content, \PHP_EOL;
});

$dispatcher = $builder->build();

$transport = Transport::create('amqp://guest:guest@localhost:5672', [
    'exchange' => 'foo',
]);

/** @var Consumer $consumer */
$consumer = $transport->consume();

$pattern  = $argv[1] ?? '#';
$priority = isset($argv[2]) ? (int) $argv[2] : 0;

if ($priority === 0) {
    $consumer->listen($pattern);
} else {
    $consumer->consume(Queue::create([
        'pattern' => $pattern,
        'args' => [
            Queue::MAX_PRIORITY => $priority,
        ],
    ]));
}

$consumer->run($dispatcher, [
    Consumer::OPTION_ATTEMPTS => 10,
    Consumer::OPTION_INTERVAL => 100000, // 100 ms
]);
