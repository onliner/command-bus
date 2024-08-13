<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote\Serializer;

use Onliner\CommandBus\Exception;
use Onliner\CommandBus\Remote\Envelope;
use Onliner\CommandBus\Remote\Serializer;

final class NativeSerializer implements Serializer
{
    public function serialize(object $command, array $headers = []): Envelope
    {
        return new Envelope(get_class($command), serialize($command), $headers);
    }

    public function unserialize(Envelope $envelope): object
    {
        $message = unserialize($envelope->payload);

        if (!$message instanceof $envelope->class) {
            throw new Exception\InvalidMessageException($envelope->class);
        }

        return $message;
    }
}
