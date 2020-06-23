<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote;

interface Serializer
{
    /**
     * @param object               $command
     * @param array<string, mixed> $headers
     *
     * @return Envelope
     */
    public function serialize(object $command, array $headers = []): Envelope;

    /**
     * @param Envelope $envelope
     *
     * @return object
     */
    public function unserialize(Envelope $envelope): object;
}
