<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote\Serializer;

use Onliner\CommandBus\Remote\Envelope;
use Onliner\CommandBus\Remote\Serializer;

final class NativeSerializer implements Serializer
{
    /**
     * {@inheritDoc}
     */
    public function serialize(object $command, array $headers = []): Envelope
    {
        return new Envelope(get_class($command), serialize($command), $headers);
    }

    /**
     * {@inheritDoc}
     */
    public function unserialize(Envelope $envelope): object
    {
        return unserialize($envelope->payload);
    }
}
