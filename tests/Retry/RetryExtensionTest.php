<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Tests\Retry;

use LogicException;
use Onliner\CommandBus\Builder;
use Onliner\CommandBus\Retry\Policy;
use Onliner\CommandBus\Retry\RetryExtension;
use Onliner\CommandBus\Tests\Command;
use PHPUnit\Framework\TestCase;

class RetryExtensionTest extends TestCase
{
    public function testNoRetryWithoutPolicy(): void
    {
        $dispatcher = (new Builder())
            ->use(new RetryExtension())
            ->handle(Command\Hello::class, function () {
                throw new LogicException();
            })
            ->build();

        self::expectException(LogicException::class);

        $dispatcher->dispatch(new Command\Hello('onliner'));
    }

    public function testRetryPolicyCalled(): void
    {
        $command = new Command\Hello('onliner');

        $policy = self::createMock(Policy::class);
        $policy
            ->expects(self::once())
            ->method('retry');

        $extension = new RetryExtension();
        $extension->policy(Command\Hello::class, $policy);

        $dispatcher = (new Builder())
            ->use($extension)
            ->handle(Command\Hello::class, function () {
                throw new LogicException();
            })
            ->build();

        $error = null;

        try {
            $dispatcher->dispatch($command);
        } catch (LogicException $error) {
        }

        self::assertNull($error);
    }
}
