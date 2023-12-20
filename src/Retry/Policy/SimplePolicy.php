<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Retry\Policy;

use Onliner\CommandBus\Context;
use Onliner\CommandBus\Retry\Policy;
use Throwable;

final class SimplePolicy implements Policy
{
    private const OPTION_ATTEMPT = 'attempt';

    /**
     * @param int $retries
     * @param int $delay
     */
    public function __construct(private int $retries, private int $delay = 0)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function retry(object $message, Context $context, Throwable $error): void
    {
        $attempt = $context->get(self::OPTION_ATTEMPT,  1);

        if ($attempt > $this->retries) {
            throw $error;
        }

        if ($this->delay > 0) {
            usleep($this->delay);
        }

        $context->dispatch($message, [
            self::OPTION_ATTEMPT => ++$attempt,
        ]);
    }
}
