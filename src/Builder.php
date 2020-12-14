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
    private $handlers = [];

    /**
     * @var array<string, Middleware>
     */
    private $middleware = [];

    /**
     * @var array<string, Extension>
     */
    private $extensions = [];

    /**
     * @param string   $command
     * @param callable $handler
     *
     * @return self
     */
    public function handle(string $command, callable $handler): self
    {
        $this->handlers[$command] = $handler;

        return $this;
    }

    /**
     * @param Middleware $middleware
     *
     * @return self
     */
    public function middleware(Middleware $middleware): self
    {
        $this->middleware[get_class($middleware)] = $middleware;

        return $this;
    }

    /**
     * @param Extension $extension
     *
     * @return self
     */
    public function use(Extension $extension): self
    {
        $this->extensions[get_class($extension)] = $extension;

        return $this;
    }

    /**
     * @return Dispatcher
     */
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

            foreach ($this->middleware as $item) {
                $resolver->register($item);
            }
        }

        return new Dispatcher($resolver);
    }
}
