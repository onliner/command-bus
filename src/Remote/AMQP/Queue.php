<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote\AMQP;

use InvalidArgumentException;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Wire\AMQPTable;

final class Queue
{
    public const
        MAX_LENGTH   = 'x-max-length',
        MAX_PRIORITY = 'x-max-priority',
        MESSAGE_TTL  = 'x-message-ttl',
        DEAD_LETTER  = 'x-dead-letter-exchange'
    ;

    private string $pattern;
    private AMQPFlags $flags;

    /**
     * @param string                $name
     * @param string|null           $pattern
     * @param AMQPFlags|null        $flags
     * @param array<string, string> $args
     */
    public function __construct(
        private string $name,
        string $pattern = null,
        AMQPFlags $flags = null,
        private array $args = []
    ) {
        $this->pattern = $pattern ?? $name;
        $this->flags   = $flags ?? AMQPFlags::default();
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return self
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

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function pattern(): string
    {
        return $this->pattern;
    }

    /**
     * @param int $flag
     *
     * @return bool
     */
    public function is(int $flag): bool
    {
        return $this->flags->is($flag);
    }

    /**
     * @param AMQPChannel $channel
     * @param Exchange    $exchange
     * @param callable    $handler
     *
     * @return void
     */
    public function consume(AMQPChannel $channel, Exchange $exchange, callable $handler): void
    {
        $channel->queue_declare(
            $this->name,
            $this->flags->is(AMQPFlags::PASSIVE),
            $this->flags->is(AMQPFlags::DURABLE),
            $this->flags->is(AMQPFlags::EXCLUSIVE),
            $this->flags->is(AMQPFlags::DELETE),
            $this->flags->is(AMQPFlags::NO_WAIT),
            new AMQPTable($this->args)
        );

        $channel->queue_bind($this->name, $exchange->name(), $this->pattern);

        $channel->basic_consume(
            $this->name,
            '',
            $this->flags->is(AMQPFlags::NO_LOCAL),
            $this->flags->is(AMQPFlags::NO_ACK),
            $this->flags->is(AMQPFlags::EXCLUSIVE),
            $this->flags->is(AMQPFlags::NO_WAIT),
            $handler
        );
    }
}
