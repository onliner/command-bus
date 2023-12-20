<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote\AMQP;

final class Route
{
    /**
     * @param string $exchange
     * @param string $name
     */
    public function __construct(private string $exchange, private string $name)
    {
    }

    /**
     * @return string
     */
    public function exchange(): string
    {
        return $this->exchange;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }
}
