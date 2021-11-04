<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote\AMQP;

use Onliner\CommandBus\Dispatcher;
use Onliner\CommandBus\Remote\Consumer;
use Onliner\CommandBus\Remote\Envelope;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Exception\AMQPConnectionClosedException;
use PhpAmqpLib\Exception\AMQPIOException;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

final class AMQPConsumer implements Consumer
{
    public const
        OPTION_ATTEMPTS = 'attempts',
        OPTION_INTERVAL = 'interval',
        OPTION_PREFETCH = 'prefetch'
    ;

    private const
        DEFAULT_ATTEMPTS = 60,
        DEFAULT_INTERVAL = 1000000
    ;

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
     * @var array<Queue>
     */
    private $queues = [];

    /**
     * @var bool
     */
    private $running = false;

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
     * @param string $pattern
     *
     * @return void
     */
    public function listen(string $pattern): void
    {
        $this->consume(new Queue($pattern, $pattern, $this->exchange->flags()));
    }

    /**
     * @param Queue $queue
     */
    public function consume(Queue $queue): void
    {
        $this->queues[] = $queue;
    }

    /**
     * {@inheritDoc}
     */
    public function run(Dispatcher $dispatcher, array $options = []): void
    {
        $this->running = true;

        $channel = $this->channel($dispatcher, $options);

        /** @phpstan-ignore-next-line */
        while ($this->running && $channel->is_consuming()) {
            try {
                $channel->wait();
            } catch (AMQPConnectionClosedException|AMQPIOException $error) {
                /** @phpstan-ignore-next-line */
                if (!$this->running) {
                    throw $error;
                }

                $channel = $this->channel($dispatcher, $options);
            } catch (Throwable $error) {
                $this->logger->error((string) $error);
            }
        }

        $channel->close();
    }

    /**
     * {@inheritDoc}
     */
    public function stop(): void
    {
        $this->running = false;
    }

    /**
     * @param Dispatcher           $dispatcher
     * @param array<string, mixed> $options
     *
     * @return AMQPChannel
     * @throws AMQPIOException
     */
    private function channel(Dispatcher $dispatcher, array $options): AMQPChannel
    {
        $channel = $this->connect($options);
        $handler = function (AMQPMessage $message) use ($dispatcher) {
            try {
                $this->handle($message, $dispatcher);
            } catch (Throwable $error) {
                $this->logger->error((string) $error);
            } finally {
                $message->ack();
            }
        };

        $this->exchange->declare($channel);

        foreach ($this->queues as $queue) {
            $queue->consume($channel, $this->exchange, $handler);
        }

        return $channel;
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return AMQPChannel
     * @throws AMQPIOException
     */
    private function connect(array $options): AMQPChannel
    {
        $maxAttempts = filter_var($options[self::OPTION_ATTEMPTS] ?? null, FILTER_VALIDATE_INT);
        $maxAttempts = $maxAttempts !== false ? $maxAttempts : self::DEFAULT_ATTEMPTS;

        $waitInterval = filter_var($options[self::OPTION_INTERVAL] ?? null, FILTER_VALIDATE_INT);
        $waitInterval = $waitInterval !== false ? $waitInterval : self::DEFAULT_INTERVAL;

        $attempt = 0;

        do {
            $attempt += 1;

            try {
                $channel = $this->connector->connect();

                $prefetchCount = filter_var($options[self::OPTION_PREFETCH] ?? null, FILTER_VALIDATE_INT);

                if ($prefetchCount !== false) {
                    $channel->basic_qos(0, $prefetchCount, false);
                }

                return $channel;
            } catch (AMQPConnectionClosedException|AMQPIOException $error) {
                usleep($waitInterval);
            }
        } while ($this->running && $attempt < $maxAttempts);

        throw new AMQPIOException();
    }

    /**
     * @param AMQPMessage $message
     * @param Dispatcher  $dispatcher
     *
     * @return void
     */
    private function handle(AMQPMessage $message, Dispatcher $dispatcher): void
    {
        $headers = $message->get('application_headers')->getNativeData();
        $headers = array_merge($headers, [
            Exchange::HEADER_EXCHANGE     => $message->getExchange(),
            Exchange::HEADER_ROUTING_KEY  => $message->getRoutingKey(),
            Exchange::HEADER_CONSUMER_TAG => $message->getConsumerTag(),
            Exchange::HEADER_DELIVERY_TAG => $message->getDeliveryTag(),
            Exchange::HEADER_REDELIVERED  => $message->isRedelivered(),
        ]);

        if (!isset($headers[Exchange::HEADER_MESSAGE_TYPE])) {
            $this->logger->warning(sprintf('Header "%s" not found in message.', Exchange::HEADER_MESSAGE_TYPE));

            return;
        }

        $type = $headers[Exchange::HEADER_MESSAGE_TYPE];

        $dispatcher->dispatch(new Envelope($type, $message->getBody(), $headers));
    }
}
