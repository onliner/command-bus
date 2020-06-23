<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Resolver;

use Onliner\CommandBus\Exception;
use Onliner\CommandBus\Resolver;

final class ClassMapResolver implements Resolver
{
    /**
     * @var array<string, callable>
     */
    private $handlers = [];

    /**
     * @param string   $class
     * @param callable $handler
     */
    public function register(string $class, callable $handler): void
    {
        $this->handlers[$class] = $handler;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve(object $command): callable
    {
        $class = get_class($command);

        return $this->handlers[$class] ?? static function () use ($class) {
            throw new Exception\UnknownHandlerException($class);
        };
    }
}
