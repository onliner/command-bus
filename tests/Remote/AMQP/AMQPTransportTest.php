<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Tests\Remote\AMQP;

use InvalidArgumentException;
use Onliner\CommandBus\Remote\AMQP\AMQPTransport;
use Onliner\CommandBus\Remote\AMQP\Connector;
use Onliner\CommandBus\Remote\AMQP\Exchange;
use Onliner\CommandBus\Remote\Envelope;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use PHPUnit\Framework\TestCase;

class AMQPTransportTest extends TestCase
{
    public function testCreate(): void
    {
        $error = null;

        try {
            AMQPTransport::create('amqp://guest:guest@localhost/vhost?timeout=1&foo=bar');
        } catch (InvalidArgumentException $error) {
        }

        self::assertNull($error);
    }

    public function testCreateWithMalformedUrl(): void
    {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Invalid transport DSN');

        AMQPTransport::create('//');
    }

    public function testSend(): void
    {
        $envelope = new Envelope('target', 'payload', [
            'foo' => 'bar',
        ]);

        $headers = $envelope->headers + [
            'x-message-type' => $envelope->type,
        ];

        $message = new AMQPMessage($envelope->payload, [
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
        ]);
        $message->set('application_headers', new AMQPTable($headers));

        $channel = self::createMock(AMQPChannel::class);
        $channel
            ->expects(self::exactly(2))
            ->method('basic_publish')
            ->with($message, 'amqp.topic', $envelope->type, false, false)
        ;

        $connector = self::createMock(Connector::class);
        $connector
            ->expects(self::exactly(2))
            ->method('connect')
            ->willReturn($channel)
        ;

        $transport = new AMQPTransport($connector, Exchange::create([]));
        $transport->send($envelope);
        $transport->send($envelope);
    }
}
