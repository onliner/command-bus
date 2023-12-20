<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Exception;

final class InvalidMessageException extends CommandBusException
{
    public function __construct(string $class)
    {
        parent::__construct(sprintf('Invalid message for class "%s".', $class));
    }
}
