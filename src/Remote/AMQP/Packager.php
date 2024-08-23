<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote\AMQP;

use Onliner\CommandBus\Remote\Envelope;
use Onliner\CommandBus\Remote\RemoteException;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPAbstractCollection;
use PhpAmqpLib\Wire\AMQPTable;

final class Packager
{
    /**
     * @deprecated
     */
    public const OPTION_LOCAL = 'local';

    public const
        OPTION_EXCHANGE = 'exchange',
        OPTION_ROUTING_KEY = 'routing_key',
        OPTION_CONSUMER_TAG = 'consumer_tag',
        OPTION_DELIVERY_TAG = 'delivery_tag'
    ;

    public function __construct(
        private int $deliveryMode = AMQPMessage::DELIVERY_MODE_PERSISTENT,
    ) {}

    public function pack(Envelope $envelope): AMQPMessage
    {
        $headers = new AMQPTable($envelope->headers);
        $headers->set(Headers::MESSAGE_TYPE, $envelope->class);

        return new AMQPMessage($envelope->payload, [
            'delivery_mode' => $this->deliveryMode,
            'application_headers' => $headers,
        ]);
    }

    public function unpack(AMQPMessage $message): Envelope
    {
        $headers = $message->get('application_headers');

        if (!$headers instanceof AMQPAbstractCollection) {
            throw new RemoteException('Message headers not found.');
        }

        $headers = $headers->getNativeData();

        if (!isset($headers[Headers::MESSAGE_TYPE])) {
            throw new RemoteException('Message type not found.');
        }

        /** @var class-string $class */
        $class = $headers[Headers::MESSAGE_TYPE];

        unset($headers[Headers::MESSAGE_TYPE]);

        return new Envelope($class, $message->getBody(), array_replace($headers, [
            self::OPTION_LOCAL => true,
            self::OPTION_EXCHANGE => $message->getExchange(),
            self::OPTION_ROUTING_KEY => $message->getRoutingKey(),
            self::OPTION_CONSUMER_TAG => $message->getConsumerTag(),
            self::OPTION_DELIVERY_TAG => $message->getDeliveryTag(),
        ]));
    }
}
