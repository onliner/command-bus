<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Tests\Remote\InMemory;

use Onliner\CommandBus\Remote\Envelope;
use Onliner\CommandBus\Remote\InMemory\InMemoryTransport;
use PHPUnit\Framework\TestCase;

class InMemoryTransportTest extends TestCase
{
    public function testSendReceive(): void
    {
        $transport = new InMemoryTransport();
        $envelope = new Envelope('target', 'payload', [
            'foo' => 'bar',
        ]);

        $transport->send($envelope);

        self::assertEquals([], $transport->receive('unknown'));
        self::assertEquals([$envelope], $transport->receive('target'));
    }
}
