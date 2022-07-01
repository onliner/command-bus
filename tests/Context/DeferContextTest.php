<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Tests\Context;

use Onliner\CommandBus\Builder;
use Onliner\CommandBus\Context;
use Onliner\CommandBus\Defer\DeferItem;
use Onliner\CommandBus\Defer\DeferQueue;
use Onliner\CommandBus\Tests\Command;
use PHPUnit\Framework\TestCase;

class DeferContextTest extends TestCase
{
    public function testDefer(): void
    {
        $root = new Context\RootContext((new Builder())->build());
        $deferred = new DeferQueue();

        $context = new Context\DeferContext($root, $deferred);
        $context->defer(new Command\Hello('bar'), [
            'foo' => 'bar',
        ]);

        $item = $deferred->pull();

        if ($item === null) {
            self::fail("Deferred item must exists");
        }

        self::assertInstanceOf(DeferItem::class, $item);

        /** @var Command\Hello $message */
        $message = $item->message;

        self::assertInstanceOf(Command\Hello::class, $message);
        self::assertSame('bar', $message->name);

        self::assertIsArray($item->options);
        self::assertSame($item->options, [
            'foo' => 'bar',
        ]);
    }
}
