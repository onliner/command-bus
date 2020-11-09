<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Middleware;

use Onliner\CommandBus\Context;
use Onliner\CommandBus\Middleware;

final class PipeMiddleware implements Middleware
{
    /**
     * {@inheritDoc}
     */
    public function call(object $message, Context $context, callable $next): void
    {
        $next($message, $context);

        if ($pipe = (array) $context->get('pipe', [])) {
            $next = array_shift($pipe);
            $options = [];

            if (!empty($pipe)) {
                $options['pipe'] = $pipe;
            }

            $context->dispatch($next, $options);
        }
    }
}
