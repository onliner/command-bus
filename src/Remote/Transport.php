<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote;

interface Transport
{
    /**
     * @param string   $route
     * @param Envelope $envelope
     */
    public function send(string $route, Envelope $envelope): void;

    /**
     * @return Consumer
     */
    public function consume(): Consumer;
}
