<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote\AMQP;

final class Route
{
    public function __construct(
        public string $exchange,
        public string $name,
    ) {}
}
