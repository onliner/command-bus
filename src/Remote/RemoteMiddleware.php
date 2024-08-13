<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote;

use Onliner\CommandBus\Context;
use Onliner\CommandBus\Middleware;

final class RemoteMiddleware implements Middleware
{
    /**
     * @param array<string> $local
     */
    public function __construct(
        private Gateway $gateway,
        private array $local = [],
    ) {}

    public function call(object $message, Context $context, callable $next): void
    {
        if ($this->isLocal(get_class($message), $context)) {
            $next($message, $context);
        } else {
            $this->gateway->send($message, $context);
        }
    }

    private function isLocal(string $class, Context $context): bool
    {
        return $context->isLocal() || in_array($class, $this->local);
    }
}
