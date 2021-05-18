<?php

declare(strict_types=1);

use Onliner\CommandBus\Builder;

require __DIR__ . '/../vendor/autoload.php';

class Foo {}

class Bar extends Foo
{
}

$dispatcher = (new Builder())
    ->handle(Foo::class, function (Foo $command) {
        echo 'Handled by parent!';
    })
    ->build();

$dispatcher->dispatch(new Bar());
