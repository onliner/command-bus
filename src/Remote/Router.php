<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote;

class Router implements Transport
{
    /**
     * @var Transport
     */
    private $default;

    /**
     * @var array<string, Transport>
     */
    private $transports = [];

    /**
     * @param Transport $default
     */
    public function __construct(Transport $default)
    {
        $this->default = $default;
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
        $this->match($envelope->type)->send($envelope);
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
