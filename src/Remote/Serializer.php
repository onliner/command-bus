<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote;

interface Serializer
{
    /**
     * @param array<string, mixed> $headers
     */
    public function serialize(object $command, array $headers = []): Envelope;
    public function unserialize(Envelope $envelope): object;
}
