<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Tests\Remote\AMQP;

use InvalidArgumentException;
use Onliner\CommandBus\Remote\AMQP\Connector;
use Onliner\CommandBus\Remote\AMQP\Headers;
use Onliner\CommandBus\Remote\AMQP\Packager;
use Onliner\CommandBus\Remote\AMQP\SimpleRouter;
use Onliner\CommandBus\Remote\AMQP\Transport;
use Onliner\CommandBus\Remote\Envelope;
use Onliner\CommandBus\Tests\Command\Hello;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use PHPUnit\Framework\TestCase;

class TransportTest extends TestCase
{
    public function testCreate(): void
    {
        $error = null;

        try {
            Transport::create('amqp://guest:guest@localhost/vhost?timeout=1&foo=bar');
        } catch (InvalidArgumentException $error) {
        }

        self::assertNull($error);
    }

    public function testCreateWithMalformedUrl(): void
    {
        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Invalid transport DSN');

        Transport::create('//');
    }

    public function testSend(): void
    {
        $envelope = new Envelope(Hello::class, 'payload', [
            'foo' => 'bar',
        ]);

        $headers = new AMQPTable($envelope->headers);
        $headers->set(Headers::MESSAGE_TYPE, $envelope->class);

        $message = new AMQPMessage($envelope->payload, [
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
            'application_headers' => $headers,
        ]);

        $channel = self::createMock(AMQPChannel::class);
        $channel
            ->expects(self::exactly(2))
            ->method('basic_publish')
            ->with($message, 'foo', strtolower(str_replace('\\', '.', $envelope->class)), false, false);

        $connector = self::createMock(Connector::class);
        $connector
            ->expects(self::exactly(2))
            ->method('connect')
            ->willReturn($channel);

        $transport = new Transport($connector, new Packager(), new SimpleRouter('foo'));
        $transport->send($envelope);
        $transport->send($envelope);
    }
}
