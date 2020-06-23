<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote;

use Exception;
use Bunny\Client;
use Bunny\Channel;
use Bunny\Message;
use Onliner\CommandBus\Dispatcher;

final class Consumer
{
    private const
        OPTION_CONSUMER_TAG = 'consumer_tag',
        OPTION_DELIVERY_TAG = 'delivery_tag',
        OPTION_REDELIVERED  = 'redelivered'
    ;

    /**
     * @var string
     */
    private $project;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var array<string>
     */
    private $queues = [];

    /**
     * @param string     $project
     * @param Client     $client
     * @param Serializer $serializer
     */
    public function __construct(string $project, Client $client, Serializer $serializer = null)
    {
        $this->project    = $project;
        $this->client     = $client;
        $this->serializer = $serializer ?? new Serializer\NativeSerializer();
    }

    /**
     * @param string $queue
     *
     * @return void
     */
    public function subscribe(string $queue): void
    {
        $this->queues[] = $queue;
    }

    /**
     * @param Dispatcher $dispatcher
     *
     * @return void
     * @throws Exception
     */
    public function run(Dispatcher $dispatcher): void
    {
        if (!$this->client->isConnected()) {
            $this->client->connect();
        }

        /** @var Channel $channel */
        $channel = $this->client->channel();

        foreach ($this->queues as $queue) {
            $queue = $this->queueName($queue);

            $channel->queueDeclare($queue);
            $channel->queueBind($queue, $this->project, $queue);

            $channel->consume(function (Message $message, Channel $channel) use ($dispatcher) {
                $this->handle($message, $channel, $dispatcher);
            }, $queue);
        }

        $this->client->run();
    }

    /**
     * @param Message    $message
     * @param Channel    $channel
     * @param Dispatcher $dispatcher
     */
    private function handle(Message $message, Channel $channel, Dispatcher $dispatcher): void
    {
        $command = $this->serializer->unserialize($message->content);
        $options = $message->headers;

        try {
            $dispatcher->dispatch($command, array_merge($options, [
                self::OPTION_REDELIVERED  => $message->redelivered,
                self::OPTION_CONSUMER_TAG => $message->consumerTag,
                self::OPTION_DELIVERY_TAG => $message->deliveryTag,
            ]));
        } finally {
            $channel->ack($message);
        }
    }

    /**
     * @param string $message
     *
     * @return string
     */
    private function queueName(string $message): string
    {
        return strtolower(str_replace('\\', '.', $message));
    }
}
