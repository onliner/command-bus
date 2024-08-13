<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote\AMQP;

use Onliner\CommandBus\Remote\Envelope;
use Onliner\CommandBus\Remote\Transport as TransportContract;
use Psr\Log\LoggerInterface;

final class Transport implements TransportContract
{
    public function __construct(
        private Connector $connector,
        private Packager $packager,
        private Router $router,
        private ?LoggerInterface $logger = null,
    ) {}

    /**
     * @param array<string, string> $routes
     */
    public static function create(string $dsn, string $exchange = '', array $routes = []): self
    {
        $router = new SimpleRouter($exchange, array_filter($routes, 'is_string'));

        return new self(Connector::create($dsn), new Packager(), $router);
    }

    public function send(Envelope $envelope): void
    {
        $message = $this->packager->pack($envelope);
        $route = $this->router->match($envelope);

        $channel = $this->connector->connect();
        $channel->basic_publish(
            $message,
            $route->exchange,
            $route->name,
            $route->mandatory,
        );
    }

    public function consume(): Consumer
    {
        return new Consumer($this->connector, $this->packager, $this->logger);
    }

    /**
     * @param Exchange|array<string, mixed> $exchange
     */
    public function declare(Exchange|array $exchange): void
    {
        if (is_array($exchange)) {
            $exchange = Exchange::create($exchange);
        }

        $exchange->declare($this->connector->connect());
    }
}
