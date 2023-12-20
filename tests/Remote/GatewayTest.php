<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Tests\Remote;

use Onliner\CommandBus\Context;
use Onliner\CommandBus\Dispatcher;
use Onliner\CommandBus\Message\DeferredIterator;
use Onliner\CommandBus\Remote\Envelope;
use Onliner\CommandBus\Remote\Gateway;
use Onliner\CommandBus\Remote\Serializer;
use Onliner\CommandBus\Remote\Transport;
use Onliner\CommandBus\Resolver\CallableResolver;
use Onliner\CommandBus\Tests\Command\Hello;
use PHPUnit\Framework\TestCase;

class GatewayTest extends TestCase
{
    public function testSend(): void
    {
        $transport  = new Transport\MemoryTransport();
        $serializer = new Serializer\NativeSerializer();

        $command = new Hello('onliner');
        $headers = [
            'foo' => 'bar',
        ];

        $dispatcher = new Dispatcher(new CallableResolver());
        $context = new Context($dispatcher, new DeferredIterator(), $headers);

        $gateway = new Gateway($transport, $serializer);
        $gateway->send($command, $context);

        $queue = $transport->receive(Hello::class);

        self::assertCount(1, $queue);

        /** @var Envelope $envelope */
        $envelope = reset($queue);

        self::assertInstanceOf(Envelope::class, $envelope);
        self::assertSame(Hello::class, $envelope->class);
        self::assertSame(serialize($command), $envelope->payload);
        self::assertSame($headers, $envelope->headers);
    }
}
