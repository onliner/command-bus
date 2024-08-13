<?php

declare(strict_types=1);

namespace Onliner\CommandBus;

interface Extension
{
    public function setup(Builder $builder): void;
}
