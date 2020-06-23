<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote\Transport;

use Onliner\CommandBus\Remote\Envelope;
use Onliner\CommandBus\Remote\Transport;

final class MemoryTransport implements Transport
{
    /**
     * @var Envelope[][]
     */
    private $messages = [];

    /**
     * {@inheritDoc}
     */
    public function send(string $queue, Envelope $envelope): void
    {
        $this->messages[$queue][] = $envelope;
    }

    /**
     * @param string $queue
     *
     * @return Envelope[]
     */
    public function receive(string $queue): array
    {
        return $this->messages[$queue] ?? [];
    }
}
