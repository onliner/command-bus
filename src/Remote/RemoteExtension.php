<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote;

use Onliner\CommandBus\Builder;
use Onliner\CommandBus\Extension;

final class RemoteExtension implements Extension
{
    private Transport $transport;
    private Serializer $serializer;

    /**
     * @var array<string>
     */
    private array $local = [
        Envelope::class,
    ];

    public function __construct(Transport $transport = null, Serializer $serializer = null)
    {
        $this->transport = $transport ?? new Transport\MemoryTransport();
        $this->serializer = $serializer ?? new Serializer\NativeSerializer();
    }

    public function local(string ...$local): void
    {
        $this->local = array_unique(array_merge($this->local, $local));
    }

    public function setup(Builder $builder): void
    {
        if ($this->transport instanceof Extension) {
            $this->transport->setup($builder);
        }

        $gateway = new Gateway($this->transport, $this->serializer);

        $builder->middleware(new RemoteMiddleware($gateway, $this->local));
        $builder->handle(Envelope::class, [$gateway, 'receive']);
    }
}
