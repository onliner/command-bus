<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote;

use Onliner\CommandBus\Context;
use Onliner\CommandBus\Remote\AMQP\Packager;

final class Gateway
{
    public function __construct(
        private Transport $transport,
        private Serializer $serializer,
    ) {}

    public function send(object $message, Context $context): void
    {
        $envelope = $this->serializer->serialize($message, $context->all());

        $this->transport->send($envelope);
    }

    public function receive(Envelope $envelope, Context $context): void
    {
        $message = $this->serializer->unserialize($envelope);

        $context->dispatch($message, array_replace($envelope->headers, [
            Packager::OPTION_LOCAL => true,
        ]));
    }
}
