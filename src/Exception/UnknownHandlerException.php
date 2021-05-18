<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Exception;

final class UnknownHandlerException extends CommandBusException
{
    public static function forCommand(object $command): self
    {
        return new self(sprintf('Handler for command "%s" not found.', get_class($command)));
    }
}
