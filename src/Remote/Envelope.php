<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote;

final class Envelope
{
    /**
     * @param class-string $class
     * @param string       $payload
     * @param array<mixed> $headers
     */
    public function __construct(public string $class, public string $payload, public array $headers = [])
    {
    }
}
