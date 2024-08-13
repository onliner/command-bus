<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Tests\Middleware;

use LogicException;
use Onliner\CommandBus\Builder;
use Onliner\CommandBus\Middleware\LoggerMiddleware;
use Onliner\CommandBus\Tests\Command;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class LoggerMiddlewareTest extends TestCase
{
    public function testLogEmpty(): void
    {
        $logger = self::createMock(LoggerInterface::class);
        $logger
            ->expects(self::never())
            ->method('log');

        $dispatcher = (new Builder())
            ->handle(Command\Hello::class, function () {})
            ->middleware(new LoggerMiddleware($logger))
            ->build();

        $dispatcher->dispatch(new Command\Hello('onliner'));
    }

    public function testLogErrors(): void
    {
        $level = LogLevel::INFO;
        $logger = self::createMock(LoggerInterface::class);
        $logger
            ->expects(self::once())
            ->method('log')
            ->with($level, 'expected', [
                'file' => __FILE__,
                'line' => 46,
            ]);

        $dispatcher = (new Builder())
            ->handle(Command\Hello::class, function () {
                throw new LogicException('expected');
            })
            ->middleware(new LoggerMiddleware($logger, $level))
            ->build();

        $error = null;

        try {
            $dispatcher->dispatch(new Command\Hello('onliner'));
        } catch (LogicException $error) {
        }

        self::assertNotNull($error);
    }
}
