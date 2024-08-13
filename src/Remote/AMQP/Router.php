<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote\AMQP;

use Onliner\CommandBus\Remote\Envelope;

interface Router
{
    public function match(Envelope $envelope): Route;
}
