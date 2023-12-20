<?php

declare(strict_types=1);

namespace Onliner\CommandBus;

use Onliner\CommandBus\Message\DeferredIterator;

final class Dispatcher
{
    /**
     * @param Resolver $resolver
     */
    public function __construct(private Resolver $resolver)
    {
    }

    /**
     * @param object               $message
     * @param array<string, mixed> $options
     *
     * @return void
     */
    public function dispatch(object $message, array $options = []): void
    {
        $handler = $this->resolver->resolve($message);
        $iterator = new DeferredIterator();
        $context = new Context($this, $iterator, $options);

        $handler($message, $context);

        foreach ($iterator as $deferred) {
            $this->dispatch($deferred->message, $deferred->options);
        }
    }
}
