<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Context;

use Onliner\CommandBus\Context;
use Onliner\CommandBus\Defer\DeferQueue;

final class DeferContext implements Context
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var DeferQueue
     */
    private $deferred;

    /**
     * @param Context    $context
     * @param DeferQueue $deferred
     */
    public function __construct(Context $context, DeferQueue $deferred)
    {
        $this->context  = $context;
        $this->deferred = $deferred;
    }

    /**
     * @param object       $message
     * @param array<mixed> $options
     *
     * @return self
     */
    public function defer(object $message, array $options = []): self
    {
        $this->deferred->push($message, $options);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function dispatch(object $message, array $options = []): void
    {
        $this->context->dispatch($message, $options);
    }

    /**
     * {@inheritDoc}
     */
    public function all(): array
    {
        return $this->context->all();
    }

    /**
     * {@inheritDoc}
     */
    public function has(string $option): bool
    {
        return $this->context->has($option);
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $option, $default = null)
    {
        return $this->context->get($option, $default);
    }

    /**
     * {@inheritDoc}
     */
    public function set(string $option, $value): Context
    {
        return $this->context->set($option, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function del(string $option): Context
    {
        return $this->context->del($option);
    }
}
