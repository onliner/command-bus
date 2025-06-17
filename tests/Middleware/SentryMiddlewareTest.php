<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Tests\Middleware;

use LogicException;
use Onliner\CommandBus\Builder;
use Onliner\CommandBus\Middleware\SentryMiddleware;
use Onliner\CommandBus\Tests\Command;
use PHPUnit\Framework\TestCase;
use Sentry\EventHint;
use Sentry\ExceptionMechanism;
use Sentry\SentrySdk;
use Sentry\State\HubInterface;

class SentryMiddlewareTest extends TestCase
{
    public function testLogEmpty(): void
    {
        $hub = self::createMock(HubInterface::class);
        SentrySdk::setCurrentHub($hub);
        $hub->expects(self::never())->method('captureException');

        $dispatcher = (new Builder())
            ->handle(Command\Hello::class, function () {})
            ->middleware(new SentryMiddleware())
            ->build();

        $dispatcher->dispatch(new Command\Hello('onliner'));
    }

    public function testCaptureErrors(): void
    {
        $hub = self::createMock(HubInterface::class);
        SentrySdk::setCurrentHub($hub);

        $hint = EventHint::fromArray([
            'mechanism' => new ExceptionMechanism(ExceptionMechanism::TYPE_GENERIC, handled: false),
        ]);
        $e = new LogicException('expected');
        $hub->expects(self::once())->method('captureException')->with($e, $hint);

        $dispatcher = (new Builder())
            ->handle(Command\Hello::class, function () use ($e) {
                throw $e;
            })
            ->middleware(new SentryMiddleware())
            ->build();

        $error = null;

        try {
            $dispatcher->dispatch(new Command\Hello('onliner'));
        } catch (LogicException $error) {
        }

        self::assertNotNull($error);
    }
}
