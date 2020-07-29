<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote\InMemory;

use Onliner\CommandBus\Dispatcher;
use Onliner\CommandBus\Remote\Consumer;
use Onliner\CommandBus\Remote\Envelope;
use Onliner\CommandBus\Remote\Transport;

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
    public function send(string $route, Envelope $envelope): void
    {
        $this->envelopes[$route][] = $envelope;
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
    public function start(Dispatcher $dispatcher): void
    {
        $this->running = true;

        foreach ($this->envelopes as $route => $items) {
            foreach ($items as $i => $item) {
                if (!$this->running()) {
                    break 2;
                }

                try {
                    $dispatcher->dispatch($item);
                } finally {
                    unset($this->envelopes[$route][$i]);
                }
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function stop(): void
    {
        $this->running = false;
    }

    /**
     * @return bool
     */
    public function running(): bool
    {
        return $this->running;
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
     * @param string $route
     *
     * @return array<Envelope>
     */
    public function receive(string $route): array
    {
        return $this->envelopes[$route] ?? [];
    }
}
