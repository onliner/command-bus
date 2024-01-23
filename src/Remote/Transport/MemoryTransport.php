<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote\Transport;

use Onliner\CommandBus\Remote\Consumer;
use Onliner\CommandBus\Remote\Envelope;
use Onliner\CommandBus\Remote\Transport;
use Throwable;

final class MemoryTransport implements Transport, Consumer
{
    /**
     * @var array<string, array<Envelope>>
     */
    private array $envelopes = [];

    /**
     * @var bool
     */
    private bool $running = false;

    /**
     * {@inheritDoc}
     */
    public function send(Envelope $envelope): void
    {
        $this->envelopes[$envelope->class][] = $envelope;
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
    public function run(callable $handler, array $options = []): void
    {
        if ($this->running) {
            return;
        }

        $this->running = true;

        do {
            foreach ($this->envelopes as $type => $envelopes) {
                foreach ($envelopes as $i => $envelope) {
                    try {
                        $handler($envelope);
                    } catch (Throwable) {
                        unset($this->envelopes[$type][$i]);
                    }
                }
            }
        } while ($this->isRunning());
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
     * @param string|null $type
     *
     * @return array<Envelope>
     */
    public function receive(string $type = null): array
    {
        return $type !== null
            ? $this->envelopes[$type] ?? []
            : $this->envelopes;
    }

    /**
     * @return bool
     */
    public function isRunning(): bool
    {
        return $this->running;
    }
}
