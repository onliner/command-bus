<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Tests\Resolver;

use Onliner\CommandBus\Exception;
use Onliner\CommandBus\Resolver;
use Onliner\CommandBus\Tests\Command;
use PHPUnit\Framework\TestCase;

class ClassMapResolverTest extends TestCase
{
    public function testResolve(): void
    {
        $command = new Command\Hello('onliner');
        $handler = function () {};

        $resolver = new Resolver\CallableResolver();
        $resolver->register(get_class($command), $handler);

        self::assertEquals($handler, $resolver->resolve($command));
    }

    public function testUnknownHandler(): void
    {
        $resolver = new Resolver\CallableResolver();

        $command = new Command\Hello('onliner');
        $handler = $resolver->resolve($command);

        self::assertIsCallable($handler);
        self::expectException(Exception\UnknownHandlerException::class);

        $handler($command);
    }
}
