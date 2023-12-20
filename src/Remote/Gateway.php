<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote;

use Onliner\CommandBus\Context;

final class Gateway
{
    public const OPTION_LOCAL = 'local';

    /**
     * @param Transport  $transport
     * @param Serializer $serializer
     */
    public function __construct(private Transport $transport, private Serializer $serializer)
    {
    }

    /**
     * @param object  $message
     * @param Context $context
     *
     * @return void
     */
    public function send(object $message, Context $context): void
    {
        $envelope = $this->serializer->serialize($message, $context->all());

        $this->transport->send($envelope);
    }

    /**
     * @param Envelope $envelope
     * @param Context  $context
     *
     * @return void
     */
    public function receive(Envelope $envelope, Context $context): void
    {
        $message = $this->serializer->unserialize($envelope);

        $context->dispatch($message, [
            self::OPTION_LOCAL => true,
        ]);
    }
}
