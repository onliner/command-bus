<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Retry;

use Onliner\CommandBus\Builder;
use Onliner\CommandBus\Extension;

final class RetryExtension implements Extension
{
    private const SORT = 100;

    private Policy $default;

    /**
     * @var array<string, Policy>
     */
    private array $policies = [];

    public function __construct(
        ?Policy $default = null,
        private int $sort = self::SORT,
    ) {
        $this->default = $default ?? new Policy\ThrowPolicy();
    }

    public function policy(string $class, Policy $policy): void
    {
        $this->policies[$class] = $policy;
    }

    public function setup(Builder $builder): void
    {
        $builder->middleware(new RetryMiddleware($this->default, $this->policies), $this->sort);
    }
}
