<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Tests\Remote\Transport;

use Onliner\CommandBus\Remote\Envelope;
use Onliner\CommandBus\Remote\Transport\MemoryTransport;
use PHPUnit\Framework\TestCase;

class MemoryTransportTest extends TestCase
{
    public function testSendReceive(): void
    {
        $transport = new MemoryTransport();
        $envelope = new Envelope('target', 'payload', [
            'foo' => 'bar',
        ]);

        $transport->send('queue', $envelope);

        self::assertEquals([], $transport->receive('unknown'));
        self::assertEquals([$envelope], $transport->receive('queue'));
    }
}
