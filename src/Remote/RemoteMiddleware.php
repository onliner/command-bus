<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote;

use Onliner\CommandBus\Context;
use Onliner\CommandBus\Middleware;

final class RemoteMiddleware implements Middleware
{
    /**
     * @var Gateway
     */
    private $gateway;

    /**
     * @var array<string>
     */
    private $local;

    /**
     * @param Gateway       $gateway
     * @param array<string> $local
     */
    public function __construct(Gateway $gateway, array $local = [])
    {
        $this->gateway = $gateway;
        $this->local   = $local;
    }

    /**
     * {@inheritDoc}
     */
    public function call(object $message, Context $context, callable $next): void
    {
        if ($this->isLocal(get_class($message), $context)) {
            $next($message, $context);
        } else {
            $this->gateway->send($message, $context);
        }
    }

    /**
     * @param string  $class
     * @param Context $context
     *
     * @return bool
     */
    private function isLocal(string $class, Context $context): bool
    {
        return $context->has(Gateway::OPTION_LOCAL) || in_array($class, $this->local);
    }
}
