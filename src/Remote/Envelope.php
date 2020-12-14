<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote;

final class Envelope
{
    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $payload;

    /**
     * @var array<mixed>
     */
    public $headers;

    /**
     * @param string       $type
     * @param string       $payload
     * @param array<mixed> $headers
     */
    public function __construct(string $type, string $payload, array $headers = [])
    {
        $this->type    = $type;
        $this->payload = $payload;
        $this->headers = $headers;
    }
}
