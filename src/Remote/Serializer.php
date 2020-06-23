<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote;

interface Serializer
{
    /**
     * @param object $command
     *
     * @return string
     */
    public function serialize(object $command): string;

    /**
     * @param string $data
     *
     * @return object
     */
    public function unserialize(string $data): object;
}
