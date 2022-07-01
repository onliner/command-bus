<?php

declare(strict_types=1);

namespace Onliner\CommandBus;

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

        $handler($message, new Context\RootContext($this, $options));
    }
}
