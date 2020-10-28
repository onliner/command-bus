<?php

namespace Onliner\CommandBus\Retry;

use Onliner\CommandBus\Context;
use Throwable;

final class ThrowPolicy implements Policy
{
    /**
     * {@inheritDoc}
     */
    public function retry(object $message, Context $context, Throwable $error): void
    {
        throw $error;
    }
}
