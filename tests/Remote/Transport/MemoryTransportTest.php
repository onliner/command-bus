<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Tests\Remote\Transport;

use Onliner\CommandBus\Remote\Envelope;
use Onliner\CommandBus\Remote\Transport\MemoryTransport;
use Onliner\CommandBus\Tests\Command\Hello;
use PHPUnit\Framework\TestCase;

class MemoryTransportTest extends TestCase
{
    public function testSendReceive(): void
    {
        $class = Hello::class;
        $transport = new MemoryTransport();
        $envelope = new Envelope($class, 'payload', [
            'foo' => 'bar',
        ]);

        $transport->send($envelope);

        self::assertEquals([], $transport->receive('unknown'));
        self::assertEquals([$envelope], $transport->receive($class));
    }
}
