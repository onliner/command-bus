<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote\AMQP;

final class Publish
{
    public function __construct(
        public string $exchange,
        public string $queue,
        public string $payload,
    ) {}

    /**
     * @param array<string, mixed> $payload
     */
    public static function create(string $exchange, string $queue, array $payload): self
    {
        return new self($exchange, $queue, json_encode($payload, JSON_THROW_ON_ERROR));
    }
}
