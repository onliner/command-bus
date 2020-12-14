<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote\Bunny;

use Bunny\Channel;
use Bunny\Client;
use Bunny\Message;
use Generator;
use Onliner\CommandBus\Dispatcher;
use Onliner\CommandBus\Remote\Consumer;
use Onliner\CommandBus\Remote\Envelope;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

final class BunnyConsumer implements Consumer
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
     * @var array<string>
     */
    private $listen = [];

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
     * @param string $queue
     *
     * @return void
     */
    public function listen(string $queue): void
    {
        $this->listen[] = $queue;
    }

    /**
     * @param Dispatcher $dispatcher
     *
     * @return void
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
                try {
                    $this->handle($message, $dispatcher);
                } catch (Throwable $error) {
                    $this->logger->error($error->getMessage());
                } finally {
                    $channel->ack($message);
                }
            }, $queue);
        }

        try {
            $this->client->run();
        } catch (Throwable $error) {
            $this->logger->error($error->getMessage());
        }
    }

    /**
     * @return void
     */
    public function stop(): void
    {
        $this->client->stop();
    }

    /**
     * @param Channel $channel
     *
     * @return Generator<string>
     */
    private function setup(Channel $channel): Generator
    {
        $exchange  = $this->options->exchange();
        $type      = $this->options->type();
        $passive   = $this->options->is(ExchangeOptions::FLAG_PASSIVE);
        $durable   = $this->options->is(ExchangeOptions::FLAG_DURABLE);
        $delete    = $this->options->is(ExchangeOptions::FLAG_DELETE);
        $internal  = $this->options->is(ExchangeOptions::FLAG_INTERNAL);
        $exclusive = $this->options->is(ExchangeOptions::FLAG_EXCLUSIVE);
        $noWait    = $this->options->is(ExchangeOptions::FLAG_NO_WAIT);
        $arguments = $this->options->args();

        $channel->exchangeDeclare($exchange, $type, $passive, $durable, $delete, $internal, $noWait, $arguments);

        foreach ($this->listen as $pattern) {
            $queue = md5($pattern);
            $channel->queueDeclare($queue, $passive, $durable, $exclusive, $delete, $noWait);
            $channel->queueBind($queue, $exchange, $pattern);

            yield $queue;
        }
    }

    /**
     * @param Message    $message
     * @param Dispatcher $dispatcher
     *
     * @return void
     */
    private function handle(Message $message, Dispatcher $dispatcher): void
    {
        $headers = array_merge($message->headers, [
            ExchangeOptions::HEADER_EXCHANGE     => $message->exchange,
            ExchangeOptions::HEADER_ROUTING_KEY  => $message->routingKey,
            ExchangeOptions::HEADER_CONSUMER_TAG => $message->consumerTag,
            ExchangeOptions::HEADER_DELIVERY_TAG => $message->deliveryTag,
            ExchangeOptions::HEADER_REDELIVERED  => $message->redelivered,
        ]);

        if (!isset($headers[ExchangeOptions::HEADER_MESSAGE_TYPE])) {
            $this->logger->warning(sprintf('Header "%s" not found in message.', ExchangeOptions::HEADER_MESSAGE_TYPE));

            return;
        }

        $type = $headers[ExchangeOptions::HEADER_MESSAGE_TYPE];

        $dispatcher->dispatch(new Envelope($type, $message->content, $headers));
    }
}
