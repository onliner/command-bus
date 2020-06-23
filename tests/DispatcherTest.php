<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Tests;

use Onliner\CommandBus\Builder;
use Onliner\CommandBus\Context;
use Onliner\CommandBus\Exception;
use PHPUnit\Framework\TestCase;

class DispatcherTest extends TestCase
{
    public function testDispatch(): void
    {
        $dispatcher = (new Builder())
            ->handle(Command\Hello::class, function ($command, $context) {
                self::assertIsObject($command);
                self::assertInstanceOf(Command\Hello::class, $command);

                self::assertIsObject($context);
                self::assertInstanceOf(Context::class, $context);

                self::assertEquals('onliner', $command->name);
            })
            ->build();

        $dispatcher->dispatch(new Command\Hello('onliner'));
    }

    public function testUnknownHandler(): void
    {
        self::expectException(Exception\UnknownHandlerException::class);

        $dispatcher = (new Builder())->build();
        $dispatcher->dispatch(new Command\Hello('onliner'));
    }
}
