<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Retry\Policy;

use Onliner\CommandBus\Context;
use Onliner\CommandBus\Retry\Policy;
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
