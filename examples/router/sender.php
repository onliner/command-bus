<?php

declare(strict_types=1);

use Onliner\CommandBus\Builder;

/** @var Builder $builder */
$builder = require __DIR__ . '/builder.php';

$dispatcher = $builder->build();
$dispatcher->dispatch(new Foo\Hello('Onliner'));
$dispatcher->dispatch(new Bar\Hello('Onliner'));
