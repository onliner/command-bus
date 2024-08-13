<?php

declare(strict_types=1);

use Onliner\CommandBus\Builder;

require __DIR__ . '/../vendor/autoload.php';

class Hello
{
    public function __construct(
        public string $message,
    ) {}
}

$dispatcher = (new Builder())
    ->handle(Hello::class, function (Hello $command) {
        echo 'Hello ' . $command->message;
    })
    ->build();

$dispatcher->dispatch(new Hello('onliner'));
