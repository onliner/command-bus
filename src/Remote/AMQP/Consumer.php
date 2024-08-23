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
        MODE_ACK = 'ack',
        MODE_NACK = 'nack',
        MODE_REJECT = 'reject'
    ;

    public const
        OPTION_ATTEMPTS = 'attempts',
        OPTION_INTERVAL = 'interval',
        OPTION_PREFETCH = 'prefetch',
        OPTION_REQUEUE = 'requeue',
        OPTION_MULTIPLE = 'multiple',
        OPTION_MODE = self::MODE_REJECT
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
        ?LoggerInterface $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @param string|array<string> $bindings
     */
    public function listen(string $name, array|string $bindings = [], ?Flags $flags = null): void
    {
        $this->consume(new Queue($name, (array) $bindings, $flags ?? Flags::default()));
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
                $this->logger->error($error->getMessage(), [
                    'type' => get_class($error),
                    'file' => $error->getFile(),
                    'line' => $error->getLine(),
                ]);
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
        $mode = $options[self::OPTION_MODE] ?? self::MODE_REJECT;
        $requeue = filter_var($options[self::OPTION_REQUEUE] ?? false, FILTER_VALIDATE_BOOLEAN);
        $multiple = filter_var($options[self::OPTION_MULTIPLE] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (!in_array($mode, [self::MODE_ACK, self::MODE_NACK, self::MODE_REJECT])) {
            $mode = self::MODE_REJECT;
        }

        $handler = function (AMQPMessage $message) use ($channel, $mode, $multiple, $requeue, $dispatcher) {
            try {
                $this->handle($message, $channel, $dispatcher);

                $message->ack($multiple);
            } catch (Throwable $error) {
                switch ($mode) {
                    case self::MODE_ACK:
                        $message->ack($multiple);
                        break;
                    case self::MODE_NACK:
                        $message->nack($requeue, $multiple);
                        break;
                    case self::MODE_REJECT:
                        $message->reject($requeue);
                        break;
                }

                $this->logger->error($error->getMessage(), [
                    'type' => get_class($error),
                    'file' => $error->getFile(),
                    'line' => $error->getLine(),
                ]);
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

        $dispatcher->dispatch($this->packager->unpack($message));
    }
}
