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
     * @var ExchangeOptions
     */
    private $options;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Connector            $connector
     * @param ExchangeOptions|null $options
     * @param LoggerInterface|null $logger
     */
    public function __construct(Connector $connector, ExchangeOptions $options = null, LoggerInterface $logger = null)
    {
        $this->connector = $connector;
        $this->options   = $options ?? ExchangeOptions::default();
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
        return new self(Connector::create($dsn), ExchangeOptions::create($options));
    }

    /**
     * {@inheritDoc}
     */
    public function send(Envelope $envelope): void
    {
        $headers = $envelope->headers + [
           ExchangeOptions::HEADER_MESSAGE_TYPE => $envelope->type,
        ];

        $message = new AMQPMessage($envelope->payload, self::MESSAGE_PROPERTIES);
        $message->set('application_headers', new AMQPTable($headers));

        $route = $this->options->route($envelope);

        $this->connector->connect()->basic_publish(
            $message,
            $route->exchange(),
            $route->name(),
            $this->options->is(ExchangeOptions::FLAG_MANDATORY),
            $this->options->is(ExchangeOptions::FLAG_IMMEDIATE)
        );
    }

    /**
     * @return Consumer
     */
    public function consume(): Consumer
    {
        return new AMQPConsumer($this->connector, $this->options, $this->logger);
    }
}
