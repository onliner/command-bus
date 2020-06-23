<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote\Serializer;

use Onliner\CommandBus\Remote\Serializer;

final class NativeSerializer implements Serializer
{
    /**
     * {@inheritDoc}
     */
    public function serialize(object $command): string
    {
        return serialize($command);
    }

    /**
     * {@inheritDoc}
     */
    public function unserialize(string $data): object
    {
        return unserialize($data);
    }
}
