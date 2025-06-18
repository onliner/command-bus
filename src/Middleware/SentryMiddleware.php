<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Middleware;

use Onliner\CommandBus\Context;
use Onliner\CommandBus\Middleware;
use Sentry\EventHint;
use Sentry\ExceptionMechanism;
use Throwable;
use function Sentry\captureException;

final class SentryMiddleware implements Middleware
{
    public function call(object $message, Context $context, callable $next): void
    {
        try {
            $next($message, $context);
        } catch (Throwable $error) {
            $hint = EventHint::fromArray([
                'mechanism' => new ExceptionMechanism(ExceptionMechanism::TYPE_GENERIC, handled: false),
            ]);

            captureException($error, $hint);

            throw $error;
        }
    }
}
