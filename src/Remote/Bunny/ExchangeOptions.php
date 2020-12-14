<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote\Bunny;

final class ExchangeOptions
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

    public const
        FLAG_PASSIVE   = 1,
        FLAG_DURABLE   = 2,
        FLAG_DELETE    = 4,
        FLAG_INTERNAL  = 8,
        FLAG_EXCLUSIVE = 16,
        FLAG_NO_WAIT   = 32,
        FLAG_MANDATORY = 64,
        FLAG_IMMEDIATE = 128
    ;

    private const OPTIONS = [
        'passive'   => self::FLAG_PASSIVE,
        'durable'   => self::FLAG_DURABLE,
        'delete'    => self::FLAG_DELETE,
        'internal'  => self::FLAG_INTERNAL,
        'exclusive' => self::FLAG_EXCLUSIVE,
        'no_wait'   => self::FLAG_NO_WAIT,
        'mandatory' => self::FLAG_MANDATORY,
        'immediate' => self::FLAG_IMMEDIATE,
    ];

    /**
     * @var string
     */
    private $exchange;

    /**
     * @var string
     */
    private $type;

    /**
     * @var int
     */
    private $flags;

    /**
     * @var array<string, string>
     */
    private $args;

    /**
     * @param string                 $exchange
     * @param string                 $type
     * @param int                    $flags
     * @param array<string, string>  $args
     */
    public function __construct(string $exchange, string $type = self::TYPE_TOPIC, int $flags = 0, array $args = [])
    {
        $this->exchange = $exchange;
        $this->type     = $type;
        $this->flags    = $flags;
        $this->args     = $args;
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return self
     */
    public static function create(array $options): self
    {
        $type     = $options['type'] ?? self::TYPE_TOPIC;
        $exchange = $options['exchange'] ?? sprintf('amqp.%s', $type);
        $args     = $options['args'] ?? [];
        $flags    = 0;

        foreach (self::OPTIONS as $key => $flag) {
            if (filter_var($options[$key] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                $flags = $flags | $flag;
            }
        }

        if ($type === self::TYPE_DELAYED && !isset($args['x-delayed-type'])) {
            $args['x-delayed-type'] = self::TYPE_TOPIC;
        }

        return new self($exchange, $type, $flags, $args);
    }

    /**
     * @return self
     */
    public static function default(): self
    {
        return self::create([]);
    }

    /**
     * @return string
     */
    public function exchange(): string
    {
        return $this->exchange;
    }

    /**
     * @return string
     */
    public function type(): string
    {
        return $this->type;
    }

    /**
     * @param int $flag
     *
     * @return bool
     */
    public function is(int $flag): bool
    {
        return ($this->flags & $flag) === $flag;
    }

    /**
     * @return array<string, string>
     */
    public function args(): array
    {
        return $this->args;
    }
}
