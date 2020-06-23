<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Tests\Command;

class Hello
{
    /**
     * @var string
     */
    public $name;

    /**
     * @param string $message
     */
    public function __construct(string $message)
    {
        $this->name = $message;
    }
}
