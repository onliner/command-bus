<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote\Bunny;

use Exception;
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
     * @var string
     */
    private $origin;

    /**
     * @var Client
     */
    private $client;

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
     * @param string     $queue
     * @param Dispatcher $dispatcher
     *
     * @return void
     * @throws Exception
     */
    public function run(string $queue, Dispatcher $dispatcher): void
    {
        if (!$this->client->isConnected()) {
            $this->client->connect();
        }

        $name = md5($queue);

        /** @var Channel $channel */
        $channel = $this->client->channel();

        $channel->exchangeDeclare($this->origin, 'topic', false, true);

        $channel->queueDeclare($name);
        $channel->queueBind($name, $this->origin, $queue);

        $channel->consume(function (Message $message, Channel $channel) use ($dispatcher) {
            $this->handle($message, $channel, $dispatcher);
        }, $name);

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

            $dispatcher->dispatch(new Envelope($this->origin, $message->content, $options));
        } finally {
            $channel->ack($message);
        }
    }
}
