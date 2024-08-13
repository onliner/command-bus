<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Message;

final class Deferred
{
    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        public object $message,
        public array $options,
    ) {}
}
