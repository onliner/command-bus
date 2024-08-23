<?php

declare(strict_types=1);

namespace Onliner\CommandBus;

interface Resolver
{
    public function resolve(object $command): callable;
}
