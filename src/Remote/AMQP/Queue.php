<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote\AMQP;

use InvalidArgumentException;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Wire\AMQPTable;

final class Queue
{
    public const
        MAX_LENGTH = 'x-max-length',
        MESSAGE_TTL = 'x-message-ttl',
        DEAD_LETTER = 'x-dead-letter-exchange',
        MAX_PRIORITY = 'x-max-priority'
    ;

    /**
     * @param array<string, string> $args
     */
    public function __construct(
        public string $name,
        public string $pattern,
        public AMQPFlags $flags,
        public array $args = [],
    ) {}

    /**
     * @param array<string, mixed> $options
     */
    public static function create(array $options): self
    {
        $pattern = $options['pattern'] ?? '#';
        $name = $options['queue'] ?? $pattern;
        $args = $options['args'] ?? [];

        if (!is_string($name)) {
            throw new InvalidArgumentException('Queue name must be a string');
        }

        if ($pattern !== null && !is_string($pattern)) {
            throw new InvalidArgumentException('Queue pattern must be a string or null');
        }

        if (!is_array($args)) {
            throw new InvalidArgumentException('Queue arguments must be an array');
        }

        return new self($name, $pattern, AMQPFlags::compute($options), $args);
    }

    public function is(int $flag): bool
    {
        return $this->flags->is($flag);
    }

    public function consume(AMQPChannel $channel, Exchange $exchange, callable $handler): void
    {
        $channel->queue_declare(
            $this->name,
            $this->is(AMQPFlags::PASSIVE),
            $this->is(AMQPFlags::DURABLE),
            $this->is(AMQPFlags::EXCLUSIVE),
            $this->is(AMQPFlags::DELETE),
            $this->is(AMQPFlags::NO_WAIT),
            new AMQPTable($this->args)
        );

        $channel->queue_bind($this->name, $exchange->name, $this->pattern);

        $channel->basic_consume(
            $this->name,
            '',
            $this->is(AMQPFlags::NO_LOCAL),
            $this->is(AMQPFlags::NO_ACK),
            $this->is(AMQPFlags::EXCLUSIVE),
            $this->is(AMQPFlags::NO_WAIT),
            $handler
        );
    }
}
