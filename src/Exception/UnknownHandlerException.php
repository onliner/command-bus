<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Exception;

final class UnknownHandlerException extends CommandBusException
{
    public function __construct(string $class)
    {
        parent::__construct(sprintf('Handler for command "%s" not found.', $class));
    }
}
