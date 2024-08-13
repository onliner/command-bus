<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Tests;

use Onliner\CommandBus\Builder;
use Onliner\CommandBus\Context;
use Onliner\CommandBus\Exception;
use Onliner\CommandBus\Extension;
use Onliner\CommandBus\Middleware;
use PHPUnit\Framework\TestCase;

class BuilderTest extends TestCase
{
    public function testBuildEmpty(): void
    {
        $dispatcher = (new Builder())->build();

        self::expectException(Exception\UnknownHandlerException::class);

        $dispatcher->dispatch(new Command\Hello('onliner'));
    }

    public function testBuildWithHandler(): void
    {
        $dispatcher = (new Builder())
            ->handle(Command\Hello::class, function (Command\Hello $command) {
                self::assertEquals($command->name, 'onliner');
            })
            ->build();

        $dispatcher->dispatch(new Command\Hello('onliner'));
    }

    public function testBuildWithMiddleware(): void
    {
        $middleware = new class() implements Middleware {
            /** @var bool */
            public $executed = false;

            public function call(object $message, Context $context, callable $next): void
            {
                $this->executed = true;
            }
        };

        $dispatcher = (new Builder())->middleware($middleware)->build();

        self::assertFalse($middleware->executed);

        $dispatcher->dispatch(new Command\Hello('onliner'));

        self::assertTrue($middleware->executed);
    }

    public function testBuildWithExtension(): void
    {
        $builder = new Builder();

        $extension = self::createMock(Extension::class);
        $extension
            ->expects(self::once())
            ->method('setup')
            ->with($builder)
        ;

        $builder->use($extension)->build();
    }
}
