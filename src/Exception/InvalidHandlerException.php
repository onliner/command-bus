<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Exception;

final class InvalidHandlerException extends CommandBusException
{
    public function __construct(string $class)
    {
        parent::__construct(sprintf('Handler for command "%s" must be callable.', $class));
    }
}
