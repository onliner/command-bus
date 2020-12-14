<?php

declare(strict_types=1);

namespace Onliner\CommandBus;

interface Extension
{
    /**
     * @param Builder $builder
     */
    public function setup(Builder $builder): void;
}
