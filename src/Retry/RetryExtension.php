<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Retry;

use Onliner\CommandBus\Builder;
use Onliner\CommandBus\Extension;

final class RetryExtension implements Extension
{
    /**
     * @var Policy
     */
    private $default;

    /**
     * @var array<string, Policy>
     */
    private $policies = [];

    /**
     * @param Policy|null $default
     */
    public function __construct(Policy $default = null)
    {
        $this->default = $default ?? new ThrowPolicy;
    }

    /**
     * @param string $class
     * @param Policy $policy
     *
     * @return void
     */
    public function policy(string $class, Policy $policy): void
    {
        $this->policies[$class] = $policy;
    }

    /**
     * {@inheritDoc}
     */
    public function setup(Builder $builder, array $options): void
    {
        $builder->middleware(new RetryMiddleware($this->default, $this->policies));
    }
}
