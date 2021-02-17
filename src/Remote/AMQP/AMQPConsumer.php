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
use PhpAmqpLib\Wire\AMQPTable;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

final class AMQPConsumer implements Consumer
{
    public const
        OPTION_ATTEMPTS = 'attempts',
        OPTION_INTERVAL = 'interval'
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
     * @var bool
     */
    private $running = false;

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
     * @param string $queue
     *
     * @return void
     */
    public function listen(string $queue): void
    {
        $this->listen[] = $queue;
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
     */
    private function channel(Dispatcher $dispatcher, array $options): AMQPChannel
    {
        $exchange  = $this->options->exchange();
        $type      = $this->options->type();
        $passive   = $this->options->is(ExchangeOptions::FLAG_PASSIVE);
        $durable   = $this->options->is(ExchangeOptions::FLAG_DURABLE);
        $delete    = $this->options->is(ExchangeOptions::FLAG_DELETE);
        $internal  = $this->options->is(ExchangeOptions::FLAG_INTERNAL);
        $exclusive = $this->options->is(ExchangeOptions::FLAG_EXCLUSIVE);
        $noWait    = $this->options->is(ExchangeOptions::FLAG_NO_WAIT);
        $arguments = new AMQPTable($this->options->args());

        $handler = function (AMQPMessage $message) use ($dispatcher) {
            try {
                $this->handle($message, $dispatcher);
            } catch (Throwable $error) {
                $this->logger->error((string) $error);
            } finally {
                $message->ack();
            }
        };

        $channel = $this->connect($options);
        $channel->exchange_declare($exchange, $type, $passive, $durable, $delete, $internal, $noWait, $arguments);

        foreach ($this->listen as $pattern) {
            $queue = md5($pattern);

            $channel->queue_declare($queue, $passive, $durable, $exclusive, $delete, $noWait);
            $channel->queue_bind($queue, $exchange, $pattern);
            $channel->basic_consume($queue, '',  false, false, false, false, $handler);
        }

        return $channel;
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return AMQPChannel
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
                return $this->connector->connect();
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
            ExchangeOptions::HEADER_EXCHANGE     => $message->getExchange(),
            ExchangeOptions::HEADER_ROUTING_KEY  => $message->getRoutingKey(),
            ExchangeOptions::HEADER_CONSUMER_TAG => $message->getConsumerTag(),
            ExchangeOptions::HEADER_DELIVERY_TAG => $message->getDeliveryTag(),
            ExchangeOptions::HEADER_REDELIVERED  => $message->isRedelivered(),
        ]);

        if (!isset($headers[ExchangeOptions::HEADER_MESSAGE_TYPE])) {
            $this->logger->warning(sprintf('Header "%s" not found in message.', ExchangeOptions::HEADER_MESSAGE_TYPE));

            return;
        }

        $type = $headers[ExchangeOptions::HEADER_MESSAGE_TYPE];

        $dispatcher->dispatch(new Envelope($type, $message->getBody(), $headers));
    }
}
