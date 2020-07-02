<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote\InMemory;

use Onliner\CommandBus\Dispatcher;
use Onliner\CommandBus\Remote\Consumer;
use Onliner\CommandBus\Remote\Envelope;

final class InMemoryConsumer implements Consumer
{
    /**
     * @var array<string, array<Envelope>>
     */
    private $envelopes = [];

    /**
     * @var string|null
     */
    private $queue;

    /**
     * @var Dispatcher|null
     */
    private $dispatcher;

    /**
     * @param string   $queue
     * @param Envelope $envelope
     *
     * @return void
     */
    public function put(string $queue, Envelope $envelope): void
    {
        if ($this->dispatcher && $this->queue && fnmatch($this->queue, $queue)) {
            $this->dispatcher->dispatch($envelope);
        } else {
            $this->envelopes[$queue][] = $envelope;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function run(string $queue, Dispatcher $dispatcher): void
    {
        $this->queue      = $queue;
        $this->dispatcher = $dispatcher;

        foreach ($this->envelopes as $queue => $items) {
            if (!fnmatch($this->queue, $queue)) {
                continue;
            }

            while ($envelope = array_shift($items)) {
                $this->dispatcher->dispatch($envelope);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function stop(): void
    {
        $this->queue = $this->dispatcher = null;
    }

    /**
     * @param string $queue
     *
     * @return array<Envelope>
     */
    public function receive(string $queue): array
    {
        return $this->envelopes[$queue] ?? [];
    }
}
