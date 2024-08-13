<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote\AMQP;

class Headers
{
    public const
        DELAY = 'x-delay',
        PRIORITY = 'x-priority',
        MESSAGE_TYPE = 'x-message-type'
    ;
}
