<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote\Bunny;

use InvalidArgumentException;
use Bunny\Channel;
use Bunny\Client;
use Onliner\CommandBus\Remote\Consumer;
use Onliner\CommandBus\Remote\Envelope;
use Onliner\CommandBus\Remote\Transport;

final class BunnyTransport implements Transport
{
    /**
     * @var string
     */
    private $origin;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var ?Channel
     */
    private $channel;

    /**
     * @param string $origin
     * @param Client $client
     */
    public function __construct(string $origin, Client $client)
    {
        $this->origin = $origin;
        $this->client = $client;
    }

    /**
     * @param string $origin
     * @param string $dsn
     *
     * @return self
     */
    public static function create(string $origin, string $dsn): self
    {
        if (!$components = parse_url($dsn)) {
            throw new InvalidArgumentException('Invalid transport DSN');
        }

        $options = [];

        if (isset($components['query'])) {
            parse_str($components['query'], $options);
        }

        return new self($origin, new Client($components + $options));
    }

    /**
     * {@inheritDoc}
     */
    public function send(string $queue, Envelope $envelope): void
    {
        $this->channel()->publish($envelope->payload, $envelope->headers, $envelope->target, $queue);
    }

    /**
     * {@inheritDoc}
     */
    public function consume(): Consumer
    {
        return new BunnyConsumer($this->origin, $this->client);
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
