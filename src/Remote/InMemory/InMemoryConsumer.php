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
     * @var string
     */
    private $queue;

    /**
     * @var Dispatcher
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
        if ($this->dispatcher && fnmatch ($this->queue, $queue)) {
            $this->dispatcher->dispatch($envelope);
        } else {
            $this->envelopes[$queue][] = $envelope;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function run(string $queue, Dispatcher $dispatcher)
    {
        $this->queue      = $queue;
        $this->dispatcher = $dispatcher;

        $this->dispatch();
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

    /**
     * @return void
     */
    private function dispatch(): void
    {
        foreach ($this->envelopes as $queue => $items) {
            if (!fnmatch ($this->queue, $queue)) {
                continue;
            }

            while ($envelope = array_shift($items)) {
                $this->dispatcher->dispatch($envelope);
            }
        }
    }
}
