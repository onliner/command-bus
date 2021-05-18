<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Resolver;

use Onliner\CommandBus\Exception;
use Onliner\CommandBus\Resolver;

final class CallableResolver implements Resolver
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

        do {
            if (isset($this->handlers[$class])) {
                return $this->handlers[$class];
            }
        } while ($class = get_parent_class($class));

        return static function () use ($command) {
            throw Exception\UnknownHandlerException::forCommand($command);
        };
    }
}
