<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Tests\Remote;

use Onliner\CommandBus\Remote\Envelope;
use Onliner\CommandBus\Tests\Command\Hello;
use PHPUnit\Framework\TestCase;

class EnvelopeTest extends TestCase
{
    public function testSerializeUnserialize(): void
    {
        $target  = Hello::class;
        $payload = 'payload';
        $headers = [
            'foo' => 'bar'
        ];

        $envelope = new Envelope($target, $payload, $headers);

        self::assertSame($target, $envelope->class);
        self::assertSame($payload, $envelope->payload);
        self::assertSame($headers, $envelope->headers);
    }
}
