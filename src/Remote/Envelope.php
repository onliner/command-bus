<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote;

final class Envelope
{
    /**
     * @var string
     */
    public $target;

    /**
     * @var string
     */
    public $payload;

    /**
     * @var array<mixed>
     */
    public $headers;

    /**
     * @param string       $target
     * @param string       $payload
     * @param array<mixed> $headers
     */
    public function __construct(string $target, string $payload, array $headers)
    {
        $this->target  = $target;
        $this->payload = $payload;
        $this->headers = $headers;
    }
}
