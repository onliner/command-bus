<?php

declare(strict_types=1);

namespace Onliner\CommandBus;

interface Resolver
{
    /**
     * @param object $command
     *
     * @return callable
     */
    public function resolve(object $command): callable;
}
