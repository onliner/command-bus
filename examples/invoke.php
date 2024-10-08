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

class HelloHandler
{
    public function __invoke(Hello $command)
    {
        echo 'Hello ' . $command->message;
    }
}

$dispatcher = (new Builder())
    ->handle(Hello::class, new HelloHandler())
    ->build();

$dispatcher->dispatch(new Hello('onliner'));
