<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote\InMemory;

use Onliner\CommandBus\Remote\Consumer;
use Onliner\CommandBus\Remote\Envelope;
use Onliner\CommandBus\Remote\Transport;

final class InMemoryTransport implements Transport
{
    /**
     * @var InMemoryConsumer
     */
    private $consumer;

    public function __construct()
    {
        $this->consumer = new InMemoryConsumer();
    }

    /**
     * {@inheritDoc}
     */
    public function send(string $queue, Envelope $envelope): void
    {
        $this->consumer->put($queue, $envelope);
    }

    /**
     * {@inheritDoc}
     */
    public function consume(): Consumer
    {
        return $this->consumer;
    }

    /**
     * @param string $queue
     *
     * @return array<Envelope>
     */
    public function receive(string $queue): array
    {
        return $this->consumer->receive($queue);
    }
}
