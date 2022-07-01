<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Middleware;

use Onliner\CommandBus\Context;
use Onliner\CommandBus\Defer\DeferQueue;
use Onliner\CommandBus\Middleware;

class DeferMiddleware implements Middleware
{
    public function call(object $message, Context $context, callable $next): void
    {
        $deferred = new DeferQueue();

        $next($message, new Context\DeferContext($context, $deferred));

        while ($defer = $deferred->pull()) {
            $context->dispatch($defer->message, $defer->options);
        }
    }
}
