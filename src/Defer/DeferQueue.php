<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Defer;

class DeferQueue
{
    /**
     * @var array<int, DeferItem>
     */
    private $messages = [];

    /**
     * @param object       $command
     * @param array<mixed> $options
     */
    public function push(object $command, array $options): void
    {
        $this->messages[] = new DeferItem($command, $options);
    }

    /**
     * @return DeferItem|null
     */
    public function pull(): ?DeferItem
    {
        return array_shift($this->messages);
    }
}
