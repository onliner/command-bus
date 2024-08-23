<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote;

final class Envelope
{
    /**
     * @param array<string, mixed> $headers
     */
    public function __construct(
        public string $class,
        public string $payload,
        public array $headers = [],
    ) {}
}
