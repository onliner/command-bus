<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote;

interface Transport
{
    public function send(Envelope $envelope): void;
    public function consume(): Consumer;
}
