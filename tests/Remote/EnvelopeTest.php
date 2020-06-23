<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Tests\Remote;

use Onliner\CommandBus\Remote\Envelope;
use PHPUnit\Framework\TestCase;

class EnvelopeTest extends TestCase
{
    public function testSerializeUnserialize(): void
    {
        $target  = 'target';
        $payload = 'payload';
        $headers = [
            'foo' => 'bar'
        ];

        $envelope = new Envelope($target, $payload, $headers);

        self::assertSame($target, $envelope->target);
        self::assertSame($payload, $envelope->payload);
        self::assertSame($headers, $envelope->headers);
    }
}
