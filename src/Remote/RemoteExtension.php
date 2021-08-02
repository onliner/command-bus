<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote;

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
     * @var array<string>
     */
    private $local = [
        Envelope::class,
    ];

    /**
     * @param Transport|null  $transport
     * @param Serializer|null $serializer
     */
    public function __construct(Transport $transport = null, Serializer $serializer = null)
    {
        $this->transport  = $transport ?? new Transport\MemoryTransport();
        $this->serializer = $serializer ?? new Serializer\NativeSerializer();
    }

    /**
     * @param string ...$local
     *
     * @return void
     */
    public function local(string ...$local): void
    {
        $this->local = array_unique(array_merge($this->local, $local));
    }

    /**
     * {@inheritDoc}
     */
    public function setup(Builder $builder): void
    {
        $gateway = new Gateway($this->transport, $this->serializer);

        $builder->middleware(new RemoteMiddleware($gateway, $this->local));
        $builder->handle(Envelope::class, [$gateway, 'receive']);
    }
}
