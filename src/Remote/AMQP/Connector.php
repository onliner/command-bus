<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote\AMQP;

use Exception;
use InvalidArgumentException;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class Connector
{
    /**
     * @var array<array<mixed>>
     */
    private $hosts;

    /**
     * @var AMQPChannel|null
     */
    private $channel;

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
     * @throws Exception
     */
    public function connect(): AMQPChannel
    {
        if (isset($this->channel) && $this->channel->is_open()) {
            return $this->channel;
        }

        return $this->channel = AMQPStreamConnection::create_connection($this->hosts)->channel();
    }
}
