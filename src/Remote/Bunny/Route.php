<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote\Bunny;

final class Route
{
    /**
     * @var string
     */
    private $exchange;

    /**
     * @var string
     */
    private $name;

    /**
     * @param string $exchange
     * @param string $name
     */
    public function __construct(string $exchange, string $name)
    {
        $this->exchange = $exchange;
        $this->name     = $name;
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
