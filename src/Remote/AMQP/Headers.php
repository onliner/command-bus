<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote\AMQP;

final class Headers
{
    public const
        DELAY = 'x-delay',
        DELIVERY_TIME = 'x-opt-delivery-time',
        PRIORITY = 'x-priority',
        MESSAGE_TYPE = 'x-message-type'
    ;
}
