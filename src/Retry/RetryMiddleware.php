<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Retry;

use Onliner\CommandBus\Context;
use Onliner\CommandBus\Middleware;

final class RetryMiddleware implements Middleware
{
    /**
     * @var array<string, Policy>
     */
    private $policies;

    /**
     * @param Policy[] $policies
     */
    public function __construct(array $policies)
    {
        $this->policies = $policies;
    }

    /**
     * {@inheritDoc}
     */
    public function call(object $message, Context $context, callable $next): void
    {
        try {
            $next($message, $context);
        } catch (\Throwable $error) {
            if (!$policy = $this->policy($message)) {
                throw $error;
            }

            $policy->retry($message, $context, $error);
        }
    }

    /**
     * @param object $message
     *
     * @return Policy|null
     */
    private function policy(object $message): ?Policy
    {
        return $this->policies[get_class($message)] ?? null;
    }
}
