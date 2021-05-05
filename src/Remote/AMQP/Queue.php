<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote\AMQP;

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

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $pattern;

    /**
     * @var AMQPFlags
     */
    private $flags;

    /**
     * @var array<string, string>
     */
    private $args;

    /**
     * @param string                $name
     * @param string                $pattern
     * @param AMQPFlags             $flags
     * @param array<string, string> $args
     */
    public function __construct(string $name, string $pattern, AMQPFlags $flags, array $args = [])
    {
        $this->name    = $name;
        $this->pattern = $pattern;
        $this->flags   = $flags;
        $this->args    = $args;
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return self
     */
    public static function create(array $options): self
    {
        $pattern = $options['pattern'] ?? '#';
        $name    = $options['queue'] ?? md5($pattern); // TODO: remove hardcoded queue name

        return new self($name, $pattern, AMQPFlags::compute($options), $options['args'] ?? []);
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
     *
     * @return void
     */
    public function declare(AMQPChannel $channel): void
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
