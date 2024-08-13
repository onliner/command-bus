<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote\AMQP;

use Onliner\CommandBus\Remote\Envelope;

final class SimpleRouter implements Router
{
    /**
     * @param array<string, string> $routes
     * @param array<string> $mandatory
     */
    public function __construct(
        private string $exchange = '',
        private array $routes = [],
        private array $mandatory = [],
    ) {}

    public function match(Envelope $envelope): Route
    {
        $target = $this->exchange($envelope->class);
        $name = strtolower(str_replace('\\', '.', $envelope->class));

        return new Route($target, $name, in_array($envelope->class, $this->mandatory));
    }

    private function exchange(string $type): string
    {
        foreach ($this->routes as $pattern => $exchange) {
            if (!fnmatch($pattern, $type, FNM_NOESCAPE)) {
                continue;
            }

            return $exchange;
        }

        return $this->exchange;
    }
}
