<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote\AMQP;

use Onliner\CommandBus\Remote\Consumer;
use Onliner\CommandBus\Remote\Envelope;
use Onliner\CommandBus\Remote\Transport;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class AMQPTransport implements Transport
{
    private const MESSAGE_PROPERTIES = [
        'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
    ];

    /**
     * @var Connector
     */
    private $connector;

    /**
     * @var Exchange
     */
    private $exchange;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Connector            $connector
     * @param Exchange             $exchange
     * @param LoggerInterface|null $logger
     */
    public function __construct(Connector $connector, Exchange $exchange, LoggerInterface $logger = null)
    {
        $this->connector = $connector;
        $this->exchange  = $exchange;
        $this->logger    = $logger ?? new NullLogger();
    }

    /**
     * @param string               $dsn
     * @param array<string, mixed> $options
     *
     * @return self
     */
    public static function create(string $dsn, array $options = []): self
    {
        return new self(Connector::create($dsn), Exchange::create($options));
    }

    /**
     * {@inheritDoc}
     */
    public function send(Envelope $envelope): void
    {
        $headers = $envelope->headers + [
            Exchange::HEADER_MESSAGE_TYPE => $envelope->type,
        ];

        $message = new AMQPMessage($envelope->payload, self::MESSAGE_PROPERTIES);
        $message->set('application_headers', new AMQPTable($headers));

        // TODO: move routing logic to separate class
        $route = $this->exchange->route($envelope);

        // TODO: add support for `mandatory` and `immediate` options
        $channel = $this->connector->connect();
        $channel->basic_publish(
            $message,
            $route->exchange(),
            $route->name(),
            false,
            false
        );
    }

    /**
     * @return Consumer
     */
    public function consume(): Consumer
    {
        return new AMQPConsumer($this->connector, $this->exchange, $this->logger);
    }
}
