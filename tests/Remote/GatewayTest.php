<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Tests\Remote;

use Onliner\CommandBus\Remote\Envelope;
use Onliner\CommandBus\Remote\Gateway;
use Onliner\CommandBus\Remote\Serializer;
use Onliner\CommandBus\Remote\Transport;
use Onliner\CommandBus\Tests\Command\Hello;
use PHPUnit\Framework\TestCase;

class GatewayTest extends TestCase
{
    public function testSend(): void
    {
        $transport  = new Transport\MemoryTransport();
        $serializer = new Serializer\NativeSerializer();

        $target  = 'target';
        $command = new Hello('onliner');
        $headers = [
            'foo' => 'bar',
        ];

        $gateway = new Gateway($transport, $serializer);
        $gateway->send($target, $command, $headers);

        $queue = $transport->receive('onliner.commandbus.tests.command.hello');

        self::assertCount(1, $queue);

        /** @var Envelope $envelope */
        $envelope = reset($queue);

        self::assertInstanceOf(Envelope::class, $envelope);
        self::assertSame($target, $envelope->target);
        self::assertSame($serializer->serialize($command), $envelope->payload);
        self::assertSame($headers, $envelope->headers);
    }
}
