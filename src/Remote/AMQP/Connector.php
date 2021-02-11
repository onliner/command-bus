<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote\AMQP;

use InvalidArgumentException;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class Connector
{
    /**
     * @var array<array<mixed>>
     */
    private $hosts;

    /**
     * @var AbstractConnection|null
     */
    private $connection;

    /**
     * @param array<array<mixed>> $hosts
     */
    public function __construct(array $hosts)
    {
        $this->hosts = $hosts;
    }

    /**
     * @param string $dsn
     *
     * @return self
     */
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

        return new self([$components]);
    }

    /**
     * @return AMQPChannel
     */
    public function connect(): AMQPChannel
    {
        if (!$this->connection) {
            $this->connection = AMQPStreamConnection::create_connection($this->hosts);
        }

        if (!$this->connection->isConnected()) {
            $this->connection->reconnect();
        }

        return $this->connection->channel();
    }
}
