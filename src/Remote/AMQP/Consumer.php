<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote\AMQP;

use Onliner\CommandBus\Dispatcher;
use Onliner\CommandBus\Remote\Consumer as ConsumerContract;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Exception\AMQPConnectionClosedException;
use PhpAmqpLib\Exception\AMQPIOException;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

final class Consumer implements ConsumerContract
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

    private LoggerInterface $logger;
    private bool $running = false;

    /**
     * @var Queue[]
     */
    private array $queues = [];

    public function __construct(
        private Connector $connector,
        private Packager $packager,
        LoggerInterface $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @param string|array<string> $bindings
     */
    public function listen(string $name, array|string $bindings = [], Flags $flags = null): void
    {
        $this->consume(new Queue($name, $name, (array) $bindings, $flags ?? Flags::default()));
    }

    public function consume(Queue $queue): void
    {
        $this->queues[] = $queue;
    }

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

    public function stop(): void
    {
        $this->running = false;
    }

    /**
     * @param array<string, mixed> $options
     */
    private function channel(Dispatcher $dispatcher, array $options): AMQPChannel
    {
        $channel = $this->connect($options);
        $handler = function (AMQPMessage $message) use ($channel, $dispatcher) {
            try {
                $this->handle($message, $channel, $dispatcher);
            } catch (Throwable $error) {
                $this->logger->error((string) $error);
            } finally {
                $message->ack();
            }
        };

        foreach ($this->queues as $queue) {
            $queue->consume($channel, $handler);
        }

        return $channel;
    }

    /**
     * @param array<string, mixed> $options
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

    private function handle(AMQPMessage $message, AMQPChannel $channel, Dispatcher $dispatcher): void
    {
        if ($message->isRedelivered()) {
            $channel->basic_publish(
                new AMQPMessage($message->body, $message->get_properties()),
                (string) $message->getExchange(),
                (string) $message->getRoutingKey(),
            );

            return;
        }

        $envelope = $this->packager->unpack($message);

        $dispatcher->dispatch($envelope);
    }
}
