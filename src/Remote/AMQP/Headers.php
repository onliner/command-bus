<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote\AMQP;

class Headers
{
    public const DELAY = 'x-delay';
    public const PRIORITY = 'x-priority';
}
