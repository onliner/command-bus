<?php

declare(strict_types=1);

use Onliner\CommandBus\Builder;
use Onliner\CommandBus\Middleware;

require __DIR__ . '/../vendor/autoload.php';

class Foo
{
}

class Bar
{
}

class Baz
{
}

$dispatcher = (new Builder())
    ->handle(Foo::class, function (Foo $command) {
        echo 'Foo!';

        yield new Bar();
    })
    ->handle(Bar::class, function (Bar $command) {
        echo 'Bar!';

        yield new Baz();
    })
    ->handle(Baz::class, function (Baz $command) {
        echo 'Baz!';
    })
    ->middleware(new Middleware\YieldMiddleware())
    ->build();

$dispatcher->dispatch(new Foo());
