<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Tests\Resolver;

use Onliner\CommandBus\Context;
use Onliner\CommandBus\Dispatcher;
use Onliner\CommandBus\Message\DeferredIterator;
use Onliner\CommandBus\Middleware;
use Onliner\CommandBus\Resolver;
use Onliner\CommandBus\Tests\Command;
use PHPUnit\Framework\TestCase;

class MiddlewareResolverTest extends TestCase
{
    public function testEmptyStack(): void
    {
        $command = new Command\Hello('onliner');
        $handler = function () {};

        $parent = self::createMock(Resolver::class);
        $parent
            ->expects(self::once())
            ->method('resolve')
            ->with($command)
            ->willReturn($handler)
        ;

        $resolver = new Resolver\MiddlewareResolver($parent);

        self::assertEquals($handler, $resolver->resolve($command));
    }

    public function testMiddleware(): void
    {
        $command = new Command\Hello('onliner');
        $handler = function () {};

        $parent = self::createMock(Resolver::class);
        $parent
            ->expects(self::once())
            ->method('resolve')
            ->with($command)
            ->willReturn($handler)
        ;

        $context = new Context(new Dispatcher($parent), new DeferredIterator());

        $middleware = self::createMock(Middleware::class);
        $middleware
            ->expects(self::once())
            ->method('call')
            ->with($command, $context, $handler)
        ;

        $resolver = new Resolver\MiddlewareResolver($parent);
        $resolver->register($middleware);

        $handler = $resolver->resolve($command);
        $handler($command, $context);
    }
}
