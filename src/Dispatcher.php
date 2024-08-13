<?php

declare(strict_types=1);

namespace Onliner\CommandBus;

use Onliner\CommandBus\Message\DeferredIterator;

final class Dispatcher
{
    public function __construct(
        private Resolver $resolver,
    ) {}

    /**
     * @param array<string, mixed> $options
     */
    public function dispatch(object $message, array $options = []): void
    {
        $iterator = new DeferredIterator();
        $context = new Context($this, $iterator, $options);

        $handler = $this->resolver->resolve($message);
        $handler($message, $context);

        foreach ($iterator as $deferred) {
            $this->dispatch($deferred->message, $deferred->options);
        }
    }
}
