<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote\AMQP\Router;

use Onliner\CommandBus\Remote\AMQP\Exchange;
use Onliner\CommandBus\Remote\AMQP\Route;
use Onliner\CommandBus\Remote\AMQP\Router;
use Onliner\CommandBus\Remote\Envelope;

final class SimpleRouter implements Router
{
    /**
     * @param array<string, string> $routes
     */
    public function __construct(private array $routes = [])
    {
    }

    /**
     * {@inheritDoc}
     */
    public function match(Envelope $envelope, Exchange $exchange): Route
    {
        $target = $this->exchange($envelope->class, $exchange->name());
        $name = strtolower(str_replace('\\', '.', $envelope->class));

        return new Route($target, $name);
    }

    /**
     * @param string $type
     * @param string $default
     *
     * @return string
     */
    private function exchange(string $type, string $default): string
    {
        foreach ($this->routes as $pattern => $exchange) {
            if (!fnmatch($pattern, $type, FNM_NOESCAPE)) {
                continue;
            }

            return $exchange;
        }

        return $default;
    }
}
