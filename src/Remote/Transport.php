<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote;

interface Transport
{
    /**
     * @param Envelope $envelope
     */
    public function send(Envelope $envelope): void;

    /**
     * @return Consumer
     */
    public function consume(): Consumer;
}
