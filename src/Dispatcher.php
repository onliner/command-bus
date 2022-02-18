<?php

declare(strict_types=1);

namespace Onliner\CommandBus;

use Onliner\CommandBus\Message\MessageIterator;

final class Dispatcher
{
    /**
     * @var Resolver
     */
    private $resolver;

    /**
     * @param Resolver $resolver
     */
    public function __construct(Resolver $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * @param object       $message
     * @param array<mixed> $options
     *
     * @return void
     */
    public function dispatch(object $message, array $options = []): void
    {
        $handler = $this->resolver->resolve($message);
        $deferred = new MessageIterator();
        $context = new Context($this, $deferred, $options);

        $handler($message, $context);

        foreach ($deferred as $item) {
            [$deferredMessage, $deferredOptions] = $item;

            $this->dispatch($deferredMessage, $deferredOptions);
        }
    }
}
