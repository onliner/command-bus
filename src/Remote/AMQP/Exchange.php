<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote\AMQP;

use Onliner\CommandBus\Remote\Envelope;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Wire\AMQPTable;

final class Exchange
{
    public const
        TYPE_TOPIC   = 'topic',
        TYPE_FANOUT  = 'fanout',
        TYPE_DIRECT  = 'direct',
        TYPE_HEADERS = 'headers',
        TYPE_DELAYED = 'x-delayed-message'
    ;

    public const
        HEADER_EXCHANGE     = 'exchange',
        HEADER_ROUTING_KEY  = 'routing_key',
        HEADER_CONSUMER_TAG = 'consumer_tag',
        HEADER_DELIVERY_TAG = 'delivery_tag',
        HEADER_REDELIVERED  = 'redelivered',
        HEADER_MESSAGE_TYPE = 'x-message-type'
    ;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var AMQPFlags
     */
    private $flags;

    /**
     * @var array<string, mixed>
     */
    private $bind;

    /**
     * @var array<string, string>
     */
    private $args;

    /**
     * @param string                 $name
     * @param string                 $type
     * @param AMQPFlags              $flags
     * @param array<string, string>  $bind
     * @param array<string, string>  $args
     */
    public function __construct(
        string $name,
        string $type,
        AMQPFlags $flags,
        array $bind = [],
        array $args = []
    ) {
        $this->name  = $name;
        $this->type  = $type;
        $this->flags = $flags;
        $this->bind  = $bind;
        $this->args  = $args;
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return self
     */
    public static function create(array $options): self
    {
        $type  = $options['type'] ?? self::TYPE_TOPIC;
        $name  = $options['exchange'] ?? sprintf('amqp.%s', $type);
        $flags = AMQPFlags::compute($options);
        $bind  = $options['bind'] ?? [];
        $args  = $options['args'] ?? [];

        if ($type === self::TYPE_DELAYED && !isset($args['x-delayed-type'])) {
            $args['x-delayed-type'] = self::TYPE_TOPIC;
        }

        return new self($name, $type, $flags, $bind, $args);
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
    public function type(): string
    {
        return $this->type;
    }

    /**
     * @return AMQPFlags
     */
    public function flags(): AMQPFlags
    {
        return $this->flags;
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
     * @param Envelope $envelope
     *
     * @return Route
     */
    public function route(Envelope $envelope): Route
    {
        $type = $envelope->type;
        $bind = $this->bind[$type] ?? $this->name;

        if (is_array($bind)) {
            [$exchange, $route] = array_values($bind);
        } else {
            $exchange = $bind;
            $route    = strtolower(str_replace('\\', '.', $type));
        }

        return new Route($exchange, $route);
    }

    /**
     * @param AMQPChannel $channel
     *
     * @return void
     */
    public function declare(AMQPChannel $channel): void
    {
        $channel->exchange_declare(
            $this->name,
            $this->type,
            $this->flags->is(AMQPFlags::PASSIVE),
            $this->flags->is(AMQPFlags::DURABLE),
            $this->flags->is(AMQPFlags::DELETE),
            $this->flags->is(AMQPFlags::INTERNAL),
            $this->flags->is(AMQPFlags::NO_WAIT),
            new AMQPTable($this->args)
        );
    }
}
