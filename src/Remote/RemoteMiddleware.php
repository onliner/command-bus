<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote;

use Onliner\CommandBus\Context;
use Onliner\CommandBus\Middleware;

final class RemoteMiddleware implements Middleware
{
    public const OPTION_REMOTE = 'remote';

    /**
     * @var Gateway
     */
    private $gateway;

    /**
     * @var array<string, string>
     */
    private $routes;

    /**
     * @param Gateway               $gateway
     * @param array<string, string> $routes
     */
    public function __construct(Gateway $gateway, array $routes)
    {
        $this->gateway = $gateway;
        $this->routes  = $routes;
    }

    /**
     * {@inheritDoc}
     */
    public function call(object $message, Context $context, callable $next): void
    {
        $class = get_class($message);

        if ($this->shouldSend($class, $context)) {
            $options = array_merge($context->all(), [
                self::OPTION_REMOTE => true,
            ]);

            $this->gateway->send($this->routes[$class], $message, $options);
        } else {
            $next($message, $context->del(self::OPTION_REMOTE));
        }
    }

    /**
     * @param string  $class
     * @param Context $context
     *
     * @return bool
     */
    private function shouldSend(string $class, Context $context): bool
    {
        return isset($this->routes[$class]) && !$context->get(self::OPTION_REMOTE, false);
    }
}
