<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote\AMQP;

use InvalidArgumentException;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Wire\AMQPTable;

final class Exchange
{
    public const
        TYPE_TOPIC = 'topic',
        TYPE_FANOUT = 'fanout',
        TYPE_DIRECT = 'direct',
        TYPE_HEADERS = 'headers',
        TYPE_DELAYED = 'x-delayed-message'
    ;

    /**
     * @param array<string, string> $args
     */
    public function __construct(
        public string $name,
        public string $type,
        public Flags $flags,
        public array $args = [],
    ) {}

    /**
     * @param array<string, mixed> $options
     */
    public static function create(array $options): self
    {
        $type = $options['type'] ?? self::TYPE_TOPIC;

        if (!is_string($type)) {
            throw new InvalidArgumentException('Exchange type must be a string');
        }

        $name = $options['name'] ?? sprintf('amqp.%s', $type);
        $args = $options['args'] ?? [];

        if (!is_string($name)) {
            throw new InvalidArgumentException('Exchange name must be a string');
        }

        if (!is_array($args)) {
            throw new InvalidArgumentException('Exchange arguments must be an array');
        }

        if ($type === self::TYPE_DELAYED && !isset($args['x-delayed-type'])) {
            $args['x-delayed-type'] = self::TYPE_TOPIC;
        }

        return new self($name, $type, Flags::compute($options), $args);
    }

    public function is(int $flag): bool
    {
        return $this->flags->is($flag);
    }

    public function declare(AMQPChannel $channel): void
    {
        $channel->exchange_declare(
            $this->name,
            $this->type,
            $this->flags->is(Flags::PASSIVE),
            $this->flags->is(Flags::DURABLE),
            $this->flags->is(Flags::DELETE),
            $this->flags->is(Flags::INTERNAL),
            $this->flags->is(Flags::NO_WAIT),
            new AMQPTable($this->args)
        );
    }
}
