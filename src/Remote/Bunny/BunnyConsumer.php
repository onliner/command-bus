<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote\Bunny;

use Bunny\Protocol\MethodQueueDeclareOkFrame;
use Generator;
use Bunny\Client;
use Bunny\Channel;
use Bunny\Message;
use Onliner\CommandBus\Dispatcher;
use Onliner\CommandBus\Remote\Consumer;
use Onliner\CommandBus\Remote\Envelope;

final class BunnyConsumer implements Consumer
{
    private const
        OPTION_CONSUMER_TAG = 'consumer_tag',
        OPTION_DELIVERY_TAG = 'delivery_tag',
        OPTION_REDELIVERED  = 'redelivered'
    ;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var ExchangeOptions
     */
    private $config;

    /**
     * @var array<string>
     */
    private $routes = [];

    /**
     * @param Client $client
     * @param ExchangeOptions $config
     */
    public function __construct(Client $client, ExchangeOptions $config)
    {
        $this->client = $client;
        $this->config = $config;
    }

    /**
     * {@inheritDoc}
     */
    public function run(Dispatcher $dispatcher): void
    {
        if (!$this->client->isConnected()) {
            $this->client->connect();
        }

        /** @var Channel $channel */
        $channel = $this->client->channel();

        foreach ($this->setup($channel) as $queue) {
            $channel->consume(function (Message $message, Channel $channel) use ($dispatcher) {
                $this->handle($message, $channel, $dispatcher);
            }, $queue);
        }

        $this->client->run();
    }

    /**
     * {@inheritDoc}
     */
    public function stop(): void
    {
        $this->client->stop();
    }

    /**
     * @param string $route
     *
     * @return self
     */
    public function bind(string $route): self
    {
        $this->routes[] = $route;

        return $this;
    }

    /**
     * @param Message    $message
     * @param Channel    $channel
     * @param Dispatcher $dispatcher
     */
    private function handle(Message $message, Channel $channel, Dispatcher $dispatcher): void
    {
        try {
            $options = array_merge($message->headers, [
                self::OPTION_REDELIVERED  => $message->redelivered,
                self::OPTION_CONSUMER_TAG => $message->consumerTag,
                self::OPTION_DELIVERY_TAG => $message->deliveryTag,
            ]);

            $dispatcher->dispatch(new Envelope($message->exchange, $message->content, $options));
        } finally {
            $channel->ack($message);
        }
    }

    /**
     * @param Channel $channel
     *
     * @return Generator<string>
     */
    private function setup(Channel $channel): Generator
    {
        $exchange = $this->config->exchange();

        $channel->exchangeDeclare(
            $exchange,
            $this->config->type(),
            $this->config->is(ExchangeOptions::FLAG_PASSIVE),
            $this->config->is(ExchangeOptions::FLAG_DURABLE),
            $this->config->is(ExchangeOptions::FLAG_DELETE),
            $this->config->is(ExchangeOptions::FLAG_INTERNAL),
            $this->config->is(ExchangeOptions::FLAG_NO_WAIT),
            $this->config->args()
        );

        foreach ($this->routes as $route) {
            /** @var MethodQueueDeclareOkFrame $frame */
            $frame = $channel->queueDeclare();

            $channel->queueBind($frame->queue, $exchange, $route);

            yield $frame->queue;
        }
    }
}
