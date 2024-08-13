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
     * @param array<string> $bindings
     * @param array<string, string> $args
     */
    public function __construct(
        public string $name,
        public string $pattern,
        private array $bindings,
        public Flags $flags,
        public array $args = [],
    ) {}

    /**
     * @param array<string, mixed> $options
     */
    public static function create(array $options): self
    {
        $pattern = $options['pattern'] ?? '#';
        $name = $options['queue'] ?? $pattern;
        $bindings = $options['bindings'] ?? [];
        $args = $options['args'] ?? [];

        if (is_string($bindings)) {
            $bindings = [$bindings];
        }

        if (!is_string($name)) {
            throw new InvalidArgumentException('Queue name must be a string');
        }

        if ($pattern !== null && !is_string($pattern)) {
            throw new InvalidArgumentException('Queue pattern must be a string or null');
        }

        if (!is_array($bindings)) {
            throw new InvalidArgumentException('Queue binding must be an array');
        }

        if (!is_array($args)) {
            throw new InvalidArgumentException('Queue arguments must be an array');
        }

        return new self($name, $pattern, $bindings, Flags::compute($options), $args);
    }

    public function is(int $flag): bool
    {
        return $this->flags->is($flag);
    }

    public function consume(AMQPChannel $channel, callable $handler): void
    {
        $channel->queue_declare(
            $this->name,
            $this->is(Flags::PASSIVE),
            $this->is(Flags::DURABLE),
            $this->is(Flags::EXCLUSIVE),
            $this->is(Flags::DELETE),
            $this->is(Flags::NO_WAIT),
            new AMQPTable($this->args)
        );

        foreach ($this->bindings as $binding) {
            $channel->queue_bind($this->name, $binding, $this->pattern);
        }

        $channel->basic_consume(
            $this->name,
            '',
            $this->is(Flags::NO_LOCAL),
            $this->is(Flags::NO_ACK),
            $this->is(Flags::EXCLUSIVE),
            $this->is(Flags::NO_WAIT),
            $handler
        );
    }
}
