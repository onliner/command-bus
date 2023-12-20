<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Resolver;

use Onliner\CommandBus\Context;
use Onliner\CommandBus\Middleware;
use Onliner\CommandBus\Resolver;

final class MiddlewareResolver implements Resolver
{
    /**
     * @var array<Middleware>
     */
    private array $stack = [];

    /**
     * @param Resolver   $resolver
     */
    public function __construct(private Resolver $resolver)
    {
    }

    /**
     * @param Middleware $middleware
     */
    public function register(Middleware $middleware): void
    {
        $this->stack[] = $middleware;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve(object $command): callable
    {
        $handler = $this->resolver->resolve($command);

        foreach ($this->stack as $middleware) {
            $handler = static function (object $message, Context $context) use ($middleware, $handler) {
                $middleware->call($message, $context, $handler);
            };
        }

        return $handler;
    }
}
