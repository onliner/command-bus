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
     * @var Client
     */
    private $client;

    /**
     * @var ExchangeOptions
     */
    private $options;

    /**
     * @var ?Channel
     */
    private $channel;

    /**
     * @param Client          $client
     * @param ExchangeOptions $options
     */
    public function __construct(Client $client, ExchangeOptions $options)
    {
        $this->client  = $client;
        $this->options = $options;
    }

    /**
     * @param string               $dsn
     * @param ExchangeOptions|null $options
     *
     * @return self
     */
    public static function create(string $dsn, ExchangeOptions $options = null): self
    {
        if (!$components = parse_url($dsn)) {
            throw new InvalidArgumentException('Invalid transport DSN');
        }

        $query = [];

        if (isset($components['query'])) {
            parse_str($components['query'], $query);
        }

        return new self(new Client($components + $query), $options ?? ExchangeOptions::create());
    }

    /**
     * {@inheritDoc}
     */
    public function send(string $route, Envelope $envelope): void
    {
        $this->channel()->publish(
            $envelope->payload,
            $envelope->headers,
            $envelope->target,
            $route,
            $this->options->is(ExchangeOptions::FLAG_MANDATORY),
            $this->options->is(ExchangeOptions::FLAG_IMMEDIATE)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function consume(): Consumer
    {
        return new BunnyConsumer($this->client, $this->options);
    }

    /**
     * @return string
     */
    public function exchange(): string
    {
        return $this->options->exchange();
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
