<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote\Transport;

use Onliner\CommandBus\Remote\Consumer;
use Onliner\CommandBus\Remote\Envelope;
use Onliner\CommandBus\Remote\Transport;

class MultiTransport implements Transport
{
    /**
     * @var array<string, Transport>
     */
    private array $transports = [];

    /**
     * @param Transport $default
     */
    public function __construct(private Transport $default)
    {
    }

    /**
     * @param string    $pattern
     * @param Transport $transport
     *
     * @return void
     */
    public function add(string $pattern, Transport $transport): void
    {
        $this->transports[$pattern] = $transport;
    }

    /**
     * {@inheritDoc}
     */
    public function send(Envelope $envelope): void
    {
        $this->match($envelope->class)->send($envelope);
    }

    /**
     * {@inheritDoc}
     */
    public function consume(): Consumer
    {
        return $this->default->consume();
    }

    /**
     * @param string $type
     *
     * @return Transport
     */
    private function match(string $type): Transport
    {
        foreach ($this->transports as $pattern => $transport) {
            if (!fnmatch($pattern, $type, FNM_NOESCAPE)) {
                continue;
            }

            return $transport;
        }

        return $this->default;
    }
}
