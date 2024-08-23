<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Retry;

use Onliner\CommandBus\Context;
use Onliner\CommandBus\Middleware;
use Throwable;

final class RetryMiddleware implements Middleware
{
    /**
     * @param array<string, Policy> $policies
     */
    public function __construct(
        private Policy $default,
        private array $policies,
    ) {}

    public function call(object $message, Context $context, callable $next): void
    {
        try {
            $next($message, $context);
        } catch (Throwable $error) {
            $policy = $this->policy($message);
            $policy->retry($message, $context, $error);
        }
    }

    private function policy(object $message): Policy
    {
        return $this->policies[get_class($message)] ?? $this->default;
    }
}
