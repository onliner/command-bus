<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote\AMQP;

use Onliner\CommandBus\Builder;
use Onliner\CommandBus\Context;
use Onliner\CommandBus\Extension;
use Onliner\CommandBus\Remote\Envelope;
use Onliner\CommandBus\Remote\Transport as TransportContract;
use Psr\Log\LoggerInterface;

final class Transport implements TransportContract, Extension
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
        $this->publish($envelope, $this->router->match($envelope));
    }

    public function publish(Envelope $envelope, Route $route): void
    {
        $message = $this->packager->pack($envelope);

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

    public function declare(Exchange $exchange): void
    {
        $exchange->declare($this->connector->connect());
    }

    public function setup(Builder $builder): void
    {
        $builder->handle(Publish::class, function (Publish $message, Context $context) {
            $this->publish(
                new Envelope(Publish::class, $message->payload, $context->all()),
                new Route($message->exchange, $message->route),
            );
        });
    }
}
