<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Tests;

use Onliner\CommandBus\Builder;
use Onliner\CommandBus\Context;
use Onliner\CommandBus\Message\Deferred;
use Onliner\CommandBus\Message\DeferredIterator;
use Onliner\CommandBus\Tests\Command;
use PHPUnit\Framework\TestCase;

class ContextTest extends TestCase
{
    public function testValues(): void
    {
        $options = [
            'foo' => 'bar',
            'baz' => 1,
        ];

        $context = new Context((new Builder())->build(), new DeferredIterator(), $options);

        self::assertEquals($options, $context->all());
    }

    public function testOptions(): void
    {
        $context = new Context((new Builder())->build(), new DeferredIterator());

        self::assertEmpty($context->all());

        $context->set('foo', 'bar');
        $context->set('bar', 1);

        self::assertTrue($context->has('foo'));
        self::assertTrue($context->has('bar'));
        self::assertFalse($context->has('baz'));

        self::assertSame('bar', $context->get('foo'));
        self::assertSame(1, $context->get('bar'));
        self::assertSame('default', $context->get('unknown', 'default'));

        $context->del('foo');

        self::assertFalse($context->has('foo'));
    }

    public function testDispatch(): void
    {
        $counter = 0;

        $dispatcher = (new Builder())
            ->handle(Command\Hello::class, function (Command\Hello $command, Context $context) use (&$counter) {
                if ($command->name === 'foo') {
                    $context->dispatch(new Command\Hello('bar'));

                    return;
                }

                self::assertSame('bar', $command->name);

                $counter++;
            })
            ->build();

        $dispatcher->dispatch(new Command\Hello('foo'));

        self::assertSame(1, $counter);
    }

    public function testDefer(): void
    {
        $iterator = new DeferredIterator();
        $context = new Context((new Builder())->build(), $iterator);
        $context->defer(new Command\Hello('bar'));

        foreach ($iterator as $deferred) {
            self::assertInstanceOf(Deferred::class, $deferred);
            self::assertInstanceOf(Command\Hello::class, $deferred->message);
            self::assertSame('bar', $deferred->message->name);
            self::assertIsArray($deferred->options);
        }
    }
}
