<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote;

use Onliner\CommandBus\Context;

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

        $context->execute($message, $envelope->headers);
    }
}
