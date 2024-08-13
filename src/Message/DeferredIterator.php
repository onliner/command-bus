<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Message;

use Generator;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<Deferred>
 */
final class DeferredIterator implements IteratorAggregate
{
    /**
     * @var array<Deferred>
     */
    private array $messages = [];

    /**
     * @param object               $message
     * @param array<string, mixed> $options
     *
     * @return self
     */
    public function append(object $message, array $options): self
    {
        $this->messages[] = new Deferred($message, $options);

        return $this;
    }

    /**
     * @return Generator<Deferred>
     */
    public function getIterator(): Generator
    {
        yield from $this->messages;
    }
}
