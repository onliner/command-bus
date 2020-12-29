<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote\InMemory;

use Onliner\CommandBus\Dispatcher;
use Onliner\CommandBus\Remote\Consumer;
use Onliner\CommandBus\Remote\Envelope;
use Onliner\CommandBus\Remote\Transport;
use Throwable;

final class InMemoryTransport implements Transport, Consumer
{
    /**
     * @var array<string, array<Envelope>>
     */
    private $envelopes = [];

    /**
     * @var bool
     */
    private $running = false;

    /**
     * {@inheritDoc}
     */
    public function send(Envelope $envelope): void
    {
        $this->envelopes[$envelope->type][] = $envelope;
    }

    /**
     * {@inheritDoc}
     */
    public function consume(): Consumer
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function run(Dispatcher $dispatcher): void
    {
        if ($this->running) {
            return;
        }

        $this->running = true;

        do {
            foreach ($this->envelopes as $type => $envelopes) {
                foreach ($envelopes as $i => $envelope) {
                    try {
                        $dispatcher->dispatch($envelope);
                    } catch (Throwable $error) {
                        unset($this->envelopes[$type][$i]);
                    }
                }
            }
        } while ($this->running);
    }

    /**
     * {@inheritDoc}
     */
    public function stop(): void
    {
        $this->running = false;
    }

    /**
     * @return void
     */
    public function clear(): void
    {
        $this->envelopes = [];
    }

    /**
     * @return bool
     */
    public function empty(): bool
    {
        return empty($this->envelopes);
    }

    /**
     * @param string $type
     *
     * @return array<Envelope>
     */
    public function receive(string $type): array
    {
        return $this->envelopes[$type] ?? [];
    }
}
