<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote\Bunny;

use Bunny\Channel;
use Bunny\Client;
use InvalidArgumentException;
use Onliner\CommandBus\Remote\Consumer;
use Onliner\CommandBus\Remote\Envelope;
use Onliner\CommandBus\Remote\Transport;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class BunnyTransport implements Transport
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var ExchangeOptions
     */
    private $options;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ?Channel
     */
    private $channel;

    /**
     * @param Client               $client
     * @param ExchangeOptions|null $options
     * @param LoggerInterface|null $logger
     */
    public function __construct(Client $client, ExchangeOptions $options = null, LoggerInterface $logger = null)
    {
        $this->client  = $client;
        $this->options = $options ?? ExchangeOptions::default();
        $this->logger  = $logger ?? new NullLogger();
    }

    /**
     * @param string               $dsn
     * @param array<string, mixed> $options
     *
     * @return self
     */
    public static function create(string $dsn, array $options = []): self
    {
        if (!$components = parse_url($dsn)) {
            throw new InvalidArgumentException('Invalid transport DSN');
        }

        if (isset($components['path'])) {
            $path = $components['path'];
            $path = $path[0] === '/' ? substr($path, 1) : $path;

            $components['path'] = $path ?: '/';
        }

        $query = [];

        if (isset($components['query'])) {
            parse_str($components['query'], $query);
        }

        return new self(new Client($components + $query), ExchangeOptions::create($options));
    }

    /**
     * {@inheritDoc}
     */
    public function send(Envelope $envelope): void
    {
        $headers = $envelope->headers + [
           ExchangeOptions::HEADER_MESSAGE_TYPE => $envelope->type,
        ];

        $route = $this->options->route($envelope);

        $this->channel()->publish(
            $envelope->payload,
            $headers,
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
        return new BunnyConsumer($this->client, $this->options, $this->logger);
    }

    /**
     * @return Channel
     */
    private function channel(): Channel
    {
        if (!$this->client->isConnected()) {
            $this->client->connect();

            $this->channel = null;
        }

        /* @phpstan-ignore-next-line */
        return $this->channel ?? $this->channel = $this->client->channel();
    }
}
