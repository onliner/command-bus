<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote\InMemory;

use Onliner\CommandBus\Dispatcher;
use Onliner\CommandBus\Remote\Consumer;
use Onliner\CommandBus\Remote\Envelope;

final class InMemoryConsumer implements Consumer
{
    /**
     * @var string
     */
    private $pattern;

    /**
     * @var array<string, array<Envelope>>
     */
    private $envelopes = [];

    /**
     * @var Dispatcher|null
     */
    private $dispatcher;

    /**
     * @param string $pattern
     */
    public function __construct(string $pattern = '*')
    {
        $this->pattern = $pattern;
    }

    /**
     * {@inheritDoc}
     */
    public function run(Dispatcher $dispatcher): void
    {
        $this->dispatcher = $dispatcher;

        foreach ($this->envelopes as $route => $items) {
            if (!fnmatch($this->pattern, $route)) {
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
        $this->dispatcher = null;
    }

    /**
     * @param string   $route
     * @param Envelope $envelope
     *
     * @return void
     */
    public function put(string $route, Envelope $envelope): void
    {
        if ($this->dispatcher && fnmatch($this->pattern, $route)) {
            $this->dispatcher->dispatch($envelope);
        } else {
            $this->envelopes[$route][] = $envelope;
        }
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
