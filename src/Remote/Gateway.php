<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote;

final class Gateway
{
    /**
     * @var Transport
     */
    private $transport;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @param Transport  $transport
     * @param Serializer $serializer
     */
    public function __construct(Transport $transport, Serializer $serializer)
    {
        $this->transport  = $transport;
        $this->serializer = $serializer;
    }

    /**
     * @param string       $target
     * @param object       $message
     * @param array<mixed> $headers
     *
     * @return void
     */
    public function send(string $target, object $message, array $headers = []): void
    {
        $payload  = $this->serializer->serialize($message);
        $envelope = new Envelope($target, $payload, $headers);

        $this->transport->send($this->queueName($message), $envelope);
    }

    /**
     * @param object $message
     *
     * @return string
     */
    private function queueName(object $message): string
    {
        return strtolower(str_replace('\\', '.', get_class($message)));
    }
}