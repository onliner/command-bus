<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote\Transport;

use Onliner\CommandBus\Dispatcher;
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
    private bool $running = false;

    public function send(Envelope $envelope): void
    {
        $this->envelopes[$envelope->class][] = $envelope;
    }

    public function consume(): Consumer
    {
        return $this;
    }

    public function run(Dispatcher $dispatcher, array $options = []): void
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
        } while ($this->isRunning());
    }

    public function stop(): void
    {
        $this->running = false;
    }

    public function clear(): void
    {
        $this->envelopes = [];
    }

    public function empty(): bool
    {
        return empty($this->envelopes);
    }

    /**
     * @return array<Envelope>
     */
    public function receive(string $type): array
    {
        return $this->envelopes[$type] ?? [];
    }

    public function isRunning(): bool
    {
        return $this->running;
    }
}
