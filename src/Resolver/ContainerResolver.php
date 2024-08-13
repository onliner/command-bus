<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Resolver;

use Onliner\CommandBus\Context;
use Onliner\CommandBus\Exception;
use Onliner\CommandBus\Resolver;
use Psr\Container\ContainerInterface;

final class ContainerResolver implements Resolver
{
    /**
     * @var array<string, string>
     */
    private array $handlers = [];

    public function __construct(
        private ContainerInterface $container,
    ) {}

    public function register(string $class, string $handler): void
    {
        $this->handlers[$class] = $handler;
    }

    public function resolve(object $command): callable
    {
        return function (object $command, Context $context) {
            $class = get_class($command);

            if (!isset($this->handlers[$class])) {
                throw new Exception\UnknownHandlerException($class);
            }

            $handler = $this->container->get($this->handlers[$class]);

            if (!is_callable($handler)) {
                throw new Exception\InvalidHandlerException($class);
            }

            $handler($command, $context);
        };
    }
}
