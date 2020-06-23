<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Tests\Remote\Transport;

use Bunny\Channel;
use Bunny\Client;
use InvalidArgumentException;
use Onliner\CommandBus\Remote\Envelope;
use Onliner\CommandBus\Remote\Transport\BunnyTransport;
use PHPUnit\Framework\TestCase;

class BunnyTransportTest extends TestCase
{
    public function testCreate(): void
    {
        $error = null;

        try {
            BunnyTransport::create('amqp://guest:guest@localhost/vhost?timeout=1&foo=bar');
        } catch (InvalidArgumentException $error) {
        }

        self::assertNull($error);
    }

    public function testCreateWithMalformedUrl(): void
    {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Invalid transport DSN');

        BunnyTransport::create('//');
    }

    public function testSend(): void
    {
        $queue    = 'queue';
        $envelope = new Envelope('target', 'payload', [
            'foo' => 'bar',
        ]);

        $channel = self::createMock(Channel::class);
        $channel
            ->expects(self::exactly(2))
            ->method('publish')
            ->with($envelope->payload, $envelope->headers, $envelope->target, $queue)
        ;

        $client = self::createMock(Client::class);
        $client
            ->expects(self::exactly(2))
            ->method('isConnected')
            ->willReturn(false, true)
        ;

        $client
            ->expects(self::once())
            ->method('connect')
        ;

        $client
            ->expects(self::once())
            ->method('channel')
            ->willReturn($channel)
        ;

        $transport = new BunnyTransport($client);

        $transport->send($queue, $envelope);
        $transport->send($queue, $envelope);
    }
}
