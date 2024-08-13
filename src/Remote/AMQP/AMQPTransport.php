<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote\AMQP;

use Onliner\CommandBus\Remote\Consumer;
use Onliner\CommandBus\Remote\Envelope;
use Onliner\CommandBus\Remote\Transport;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use Psr\Log\LoggerInterface;

final class AMQPTransport implements Transport
{
    private const MESSAGE_PROPERTIES = [
        'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
    ];

    public function __construct(
        private Connector $connector,
        private Exchange $exchange,
        private Router $router,
        private ?LoggerInterface $logger = null,
    ) {}

    /**
     * @param array<string, mixed> $options
     */
    public static function create(string $dsn, array $options = []): self
    {
        if (!is_array($routes = $options['routes'] ?? false)) {
            $routes = [];
        }

        $router = new SimpleRouter(array_filter($routes, 'is_string'));

        return new self(Connector::create($dsn), Exchange::create($options), $router);
    }

    public function send(Envelope $envelope): void
    {
        $headers = $envelope->headers + [
            Exchange::HEADER_MESSAGE_TYPE => $envelope->class,
        ];

        $message = new AMQPMessage($envelope->payload, self::MESSAGE_PROPERTIES);
        $message->set('application_headers', new AMQPTable($headers));

        $route = $this->router->match($envelope, $this->exchange);

        // TODO: add support for `mandatory` and `immediate` options
        $channel = $this->connector->connect();
        $channel->basic_publish(
            $message,
            $route->exchange,
            $route->name,
        );
    }

    public function consume(): Consumer
    {
        return new AMQPConsumer($this->connector, $this->exchange, $this->logger);
    }
}
