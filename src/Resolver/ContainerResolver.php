<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Resolver;

use Onliner\CommandBus\Exception;
use Onliner\CommandBus\Resolver;
use Psr\Container\ContainerInterface;

final class ContainerResolver implements Resolver
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var array<string, string>
     */
    private $handlers = [];

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $class
     * @param string $handler
     */
    public function register(string $class, string $handler): void
    {
        $this->handlers[$class] = $handler;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve(object $command): callable
    {
        return function ($command, $context) {
            $class = get_class($command);

            if (!isset($this->handlers[$class])) {
                throw new Exception\UnknownHandlerException($class);
            }

            $handler = $this->container->get($this->handlers[$class]);
            $handler($command, $context);
        };
    }
}
