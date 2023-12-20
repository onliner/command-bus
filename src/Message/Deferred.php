<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Message;

class Deferred
{
    /**
     * @param object               $message
     * @param array<string, mixed> $options
     */
    public function __construct(
        public object $message,
        public array $options,
    ) {
    }
}
