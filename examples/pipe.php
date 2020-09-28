<?php

declare(strict_types=1);

use Onliner\CommandBus\Builder;
use Onliner\CommandBus\Middleware\PipeMiddleware;

require __DIR__ . '/../vendor/autoload.php';

class Hello
{
    public $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }
}

class HelloHandler
{
    public function __invoke(Hello $command)
    {
        echo 'Hello ', $command->message, \PHP_EOL;
    }
}

$dispatcher = (new Builder())
    ->middleware(new PipeMiddleware())
    ->handle(Hello::class, new HelloHandler())
    ->build();

$dispatcher->dispatch(new Hello('foo'), [
    'pipe' => [new Hello('bar'), new Hello('baz')]
]);
