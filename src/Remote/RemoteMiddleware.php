<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote;

use Onliner\CommandBus\Context;
use Onliner\CommandBus\Middleware;
use Onliner\CommandBus\Remote\AMQP\Packager;

final class RemoteMiddleware implements Middleware
{
    /**
     * @param array<string> $local
     */
    public function __construct(
        private Gateway $gateway,
        private array $local = [],
    ) {}

    public function call(object $message, Context $context, callable $next): void
    {
        if ($context->has(Packager::OPTION_LOCAL) || in_array(get_class($message), $this->local)) {
            $next($message, $context);
        } else {
            $this->gateway->send($message, $context);
        }
    }
}
