<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Middleware;

use Generator;
use InvalidArgumentException;
use Onliner\CommandBus\Context;
use Onliner\CommandBus\Middleware;
use Throwable;

final class YieldMiddleware implements Middleware
{
    /**
     * {@inheritDoc}
     */
    public function call(object $message, Context $context, callable $next): void
    {
        $result = $next($message, $context);

        if (!$result instanceof Generator) {
            return;
        }

        do {
            $this->tick($result, $context);
        } while ($result->valid());
    }

    /**
     * @param Generator<object> $generator
     * @param Context           $context
     */
    private function tick(Generator $generator, Context $context): void
    {
        $current = $generator->current();

        if (!is_object($current)) {
            throw new InvalidArgumentException('Invalid value yielded from handler');
        }

        try {
            $context->dispatch($current);
        } catch (Throwable $error) {
            // https://github.com/phpstan/phpstan/issues/3418
            /* @phpstan-ignore-next-line */
            $generator->throw($error);

            return;
        }

        $generator->next();
    }
}
