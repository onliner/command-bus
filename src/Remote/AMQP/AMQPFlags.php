<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote\AMQP;

class AMQPFlags
{
    public const
        PASSIVE = 1,
        DURABLE = 2,
        DELETE = 4,
        INTERNAL = 8,
        EXCLUSIVE = 16,
        NO_WAIT = 32,
        NO_LOCAL = 64,
        NO_ACK = 128
    ;

    private const OPTIONS = [
        'passive' => self::PASSIVE,
        'durable' => self::DURABLE,
        'delete' => self::DELETE,
        'internal' => self::INTERNAL,  // Used only for declare exchange
        'exclusive' => self::EXCLUSIVE, // Used only for declare queue
        'no_wait' => self::NO_WAIT,
        'no_local' => self::NO_LOCAL,  // Used only for consume
        'no_ack' => self::NO_ACK,    // Used only for consume
    ];

    public function __construct(
        private int $value,
    ) {}

    public static function default(): self
    {
        return new self(0);
    }

    /**
     * @param array<string, mixed> $options
     */
    public static function compute(array $options = []): self
    {
        $value = 0;

        foreach (self::OPTIONS as $key => $flag) {
            if (filter_var($options[$key] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                $value = $value | $flag;
            }
        }

        return new self($value);
    }

    public function is(int $flag): bool
    {
        return ($this->value & $flag) === $flag;
    }
}
