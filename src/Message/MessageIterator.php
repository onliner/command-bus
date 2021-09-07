<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Message;

use Generator;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<array>
 */
class MessageIterator implements IteratorAggregate
{
    /**
     * @var array<array>
     */
    private $messages = [];

    /**
     * @param object       $message
     * @param array<mixed> $options
     *
     * @return self<array>
     */
    public function append(object $message, array $options): self
    {
        $this->messages[] = [$message, $options];

        return $this;
    }

    /**
     * @return Generator<array>
     */
    public function getIterator(): Generator
    {
        yield from $this->messages;
    }
}
