<?php

declare(strict_types=1);

namespace Onliner\CommandBus;

use Onliner\CommandBus\Resolver\CallableResolver;
use Onliner\CommandBus\Resolver\MiddlewareResolver;

final class Builder
{
    /**
     * @var array<string, callable>
     */
    private array $handlers = [];

    /**
     * @var array<int, array<Middleware>>
     */
    private array $middleware = [];

    /**
     * @var array<string, Extension>
     */
    private array $extensions = [];

    public function handle(string $command, callable $handler): self
    {
        $this->handlers[$command] = $handler;

        return $this;
    }

    public function middleware(Middleware $middleware, int $sort = 0): self
    {
        $this->middleware[$sort][] = $middleware;

        return $this;
    }

    public function use(Extension $extension): self
    {
        $this->extensions[get_class($extension)] = $extension;

        return $this;
    }

    public function build(): Dispatcher
    {
        foreach ($this->extensions as $extension) {
            $extension->setup($this);
        }

        $resolver = new CallableResolver();

        foreach ($this->handlers as $command => $handler) {
            $resolver->register($command, $handler);
        }

        if (!empty($this->middleware)) {
            $resolver = new MiddlewareResolver($resolver);

            ksort($this->middleware);

            foreach ($this->middleware as $middlewares) {
                foreach ($middlewares as $item) {
                    $resolver->register($item);
                }
            }
        }

        return new Dispatcher($resolver);
    }
}
