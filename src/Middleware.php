<?php

declare(strict_types=1);

namespace Onliner\CommandBus;

interface Middleware
{
    public function call(object $message, Context $context, callable $next): void;
}
