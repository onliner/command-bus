<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Tests;

use Onliner\CommandBus\Builder;
use Onliner\CommandBus\Context;
use Onliner\CommandBus\Exception;
use Onliner\CommandBus\Middleware\DeferMiddleware;
use PHPUnit\Framework\TestCase;

class DispatcherTest extends TestCase
{
    public function testDispatch(): void
    {
        $dispatcher = (new Builder())
            ->handle(Command\Hello::class, function ($command, Context $context) {
                self::assertIsObject($command);
                self::assertInstanceOf(Command\Hello::class, $command);

                self::assertIsObject($context);
                self::assertInstanceOf(Context::class, $context);

                self::assertEquals('onliner', $command->name);
            })
            ->build();

        $dispatcher->dispatch(new Command\Hello('onliner'));
    }

    public function testDefer(): void
    {
        $result = '';
        $handler = function (Command\Hello $command, Context\DeferContext $context) use (&$result) {
            $result .= $command->name;

            if ($command->name === 'foo') {
                $context->defer(new Command\Hello('baz'));
                $context->dispatch(new Command\Hello('bar'));
            }
        };

        $dispatcher = (new Builder())
            ->middleware(new DeferMiddleware())
            ->handle(Command\Hello::class, $handler)
            ->build();

        $dispatcher->dispatch(new Command\Hello('foo'));

        self::assertSame('foobarbaz', $result);
    }

    public function testDeferExecuteOnException(): void
    {
        $result = '';
        $handler = function (Command\Hello $command, Context\DeferContext $context) use (&$result) {
            $result .= $command->name;

            $context->defer(new Command\Hello('bar'));

            throw new \RuntimeException('Failed');
        };

        try {
            $dispatcher = (new Builder())
                ->middleware(new DeferMiddleware())
                ->handle(Command\Hello::class, $handler)
                ->build();

            $dispatcher->dispatch(new Command\Hello('foo'));
        } catch (\Throwable $e) {
            //do nothing
        }

        self::assertSame('foo', $result);
    }

    public function testUnknownHandler(): void
    {
        $this->expectException(Exception\UnknownHandlerException::class);

        $dispatcher = (new Builder())->build();
        $dispatcher->dispatch(new Command\Hello('onliner'));
    }
}
