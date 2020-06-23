<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote;

use Bunny\Client;
use Onliner\CommandBus\Builder;
use Onliner\CommandBus\Extension;

final class RemoteExtension implements Extension
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
     * @var array<string, string>
     */
    private $routes = [];

    /**
     * @param Transport  $transport
     * @param Serializer $serializer
     */
    public function __construct(Transport $transport = null, Serializer $serializer = null)
    {
        $this->transport  = $transport ?? new Transport\MemoryTransport();
        $this->serializer = $serializer ?? new Serializer\NativeSerializer();
    }

    /**
     * @param string $message
     * @param string $exchange
     *
     * @return void
     */
    public function route(string $message, string $exchange): void
    {
        $this->routes[$message] = $exchange;
    }

    /**
     * {@inheritDoc}
     */
    public function setup(Builder $builder, array $options): void
    {
        $gateway = new Gateway($this->transport, $this->serializer);

        $builder->middleware(new RemoteMiddleware($gateway, $this->routes));
    }
}
