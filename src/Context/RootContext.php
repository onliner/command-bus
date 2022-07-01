<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Context;

use Onliner\CommandBus\Context;
use Onliner\CommandBus\Dispatcher;

final class RootContext implements Context
{
    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * @var array<mixed>
     */
    private $options;

    /**
     * @internal
     *
     * @param Dispatcher   $dispatcher
     * @param array<mixed> $options
     */
    public function __construct(Dispatcher $dispatcher, array $options = [])
    {
        $this->dispatcher = $dispatcher;
        $this->options = $options;
    }

    /**
     * {@inheritDoc}
     */
    public function dispatch(object $message, array $options = []): void
    {
        $this->dispatcher->dispatch($message, $options);
    }

    /**
     * {@inheritDoc}
     */
    public function all(): array
    {
        return $this->options;
    }

    /**
     * {@inheritDoc}
     */
    public function has(string $option): bool
    {
        return array_key_exists($option, $this->options);
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $option, $default = null)
    {
        return $this->options[$option] ?? $default;
    }

    /**
     * {@inheritDoc}
     */
    public function set(string $option, $value): Context
    {
        $this->options[$option] = $value;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function del(string $option): Context
    {
        unset($this->options[$option]);

        return $this;
    }
}
