<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote;

interface Transport
{
    /**
     * @param string   $queue
     * @param Envelope $envelope
     */
    public function send(string $queue, Envelope $envelope): void;

    /**
     * @return Consumer
     */
    public function consume(): Consumer;
}
