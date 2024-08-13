<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote\AMQP;

final class Publish
{
    public function __construct(
        public string $exchange,
        public string $route,
        public string $payload,
    ) {}

    /**
     * @param array<string, mixed> $payload
     */
    public static function create(string $exchange, string $route, array $payload): self
    {
        return new self($exchange, $route, json_encode($payload, JSON_THROW_ON_ERROR));
    }
}
