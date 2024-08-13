<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Retry;

use Onliner\CommandBus\Context;
use Throwable;

interface Policy
{
    public function retry(object $message, Context $context, Throwable $error): void;
}
