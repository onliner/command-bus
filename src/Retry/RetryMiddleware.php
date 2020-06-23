<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Retry;

use Onliner\CommandBus\Context;
use Onliner\CommandBus\Middleware;
use Throwable;

final class RetryMiddleware implements Middleware
{
    /**
     * @var Policy
     */
    private $default;

    /**
     * @var array<string, Policy>
     */
    private $policies;

    /**
     * @param Policy   $default
     * @param Policy[] $policies
     */
    public function __construct(Policy $default, array $policies)
    {
        $this->default  = $default;
        $this->policies = $policies;
    }

    /**
     * {@inheritDoc}
     */
    public function call(object $message, Context $context, callable $next): void
    {
        try {
            $next($message, $context);
        } catch (Throwable $error) {
            $policy = $this->policy($message);
            $policy->retry($message, $context, $error);
        }
    }

    /**
     * @param object $message
     *
     * @return Policy
     */
    private function policy(object $message): Policy
    {
        return $this->policies[get_class($message)] ?? $this->default;
    }
}
