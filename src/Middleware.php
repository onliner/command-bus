<?php

declare(strict_types=1);

namespace Onliner\CommandBus;

interface Middleware
{
    /**
     * @param object   $message
     * @param Context  $context
     * @param callable $next
     *
     * @return void
     */
    public function call(object $message, Context $context, callable $next): void;
}
