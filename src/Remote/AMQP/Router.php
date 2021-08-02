<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote\AMQP;

use Onliner\CommandBus\Remote\Envelope;

interface Router
{
    /**
     * @param Envelope $envelope
     * @param Exchange $exchange
     *
     * @return Route
     */
    public function match(Envelope $envelope, Exchange $exchange): Route;
}
