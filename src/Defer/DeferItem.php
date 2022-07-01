<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Defer;

class DeferItem
{
    /**
     * @var object
     */
    public $message;

    /**
     * @var array<mixed>
     */
    public $options;

    /**
     * @param object       $message
     * @param array<mixed> $options
     */
    public function __construct(object $message, array $options)
    {
        $this->message = $message;
        $this->options = $options;
    }
}
