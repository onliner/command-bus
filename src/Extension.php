<?php

declare(strict_types=1);

namespace Onliner\CommandBus;

interface Extension
{
    /**
     * @param Builder      $builder
     * @param array<mixed> $options
     */
    public function setup(Builder $builder, array $options): void;
}
