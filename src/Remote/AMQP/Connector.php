<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote\AMQP;

use InvalidArgumentException;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Connection\Heartbeat;

class Connector
{
    private ?AMQPChannel $channel = null;
    private ?Heartbeat\AbstractSignalHeartbeatSender $heartbeats = null;

    /**
     * @param array<array<mixed>> $hosts
     * @param array<string|int, mixed> $options
     */
    public function __construct(
        private array $hosts,
        private array $options,
    ) {}

    public static function create(string $dsn): self
    {
        if (!$components = parse_url($dsn)) {
            throw new InvalidArgumentException('Invalid transport DSN');
        }

        if (isset($components['path'])) {
            $path = $components['path'];
            $path = $path[0] === '/' ? substr($path, 1) : $path;

            $components['vhost'] = $path ?: '/';

            unset($components['path']);
        }

        if (isset($components['pass'])) {
            $components['password'] = $components['pass'];

            unset($components['pass']);
        }

        $options = [];

        if (isset($components['query'])) {
            parse_str($components['query'], $options);
        }

        if (PHP_SAPI !== 'cli') {
            $options['heartbeat'] = 0;
        }

        return new self([$components], $options);
    }

    public function connect(): AMQPChannel
    {
        if (isset($this->channel) && $this->channel->is_open()) {
            return $this->channel;
        }

        /** @var AMQPStreamConnection $connection */
        $connection = AMQPStreamConnection::create_connection($this->hosts, $this->options);

         if ($connection->getHeartbeat() > 0) {
            $this->heartbeats = match ($this->options['heartbeat_sender'] ?? null) {
                'sig' => new Heartbeat\SIGHeartbeatSender($connection),
                default => new Heartbeat\PCNTLHeartbeatSender($connection),
            };

            $this->heartbeats->register();
        }

        return $this->channel = $connection->channel();
    }

    public function __destruct()
    {
        $this->heartbeats?->unregister();
        $this->channel?->close();
    }
}
