<?php

declare(strict_types=1);

namespace Onliner\CommandBus;

use Onliner\CommandBus\Message\MessageIterator;

final class Context
{
    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * @var MessageIterator
     */
    private $deferred;

    /**
     * @var array<mixed>
     */
    private $options;

    /**
     * @internal
     *
     * @param Dispatcher             $dispatcher
     * @param MessageIterator<array> $deferred
     * @param array<mixed>           $options
     */
    public function __construct(Dispatcher $dispatcher, MessageIterator $deferred, array $options = [])
    {
        $this->dispatcher = $dispatcher;
        $this->deferred = $deferred;
        $this->options = $options;
    }

    /**
     * @param object       $message
     * @param array<mixed> $options
     *
     * @return void
     */
    public function dispatch(object $message, array $options = []): void
    {
        $this->dispatcher->dispatch($message, $options);
    }

    /**
     * @param object       $message
     * @param array<mixed> $options
     *
     * @return self
     */
    public function defer(object $message, array $options = []): self
    {
        $this->deferred->append($message, $options);

        return $this;
    }

    /**
     * @return array<mixed>
     */
    public function all(): array
    {
        return $this->options;
    }

    /**
     * @param string $option
     *
     * @return bool
     */
    public function has(string $option): bool
    {
        return array_key_exists($option, $this->options);
    }

    /**
     * @param string $option
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get(string $option, $default = null)
    {
        return $this->options[$option] ?? $default;
    }

    /**
     * @param string $option
     * @param mixed  $value
     *
     * @return self
     */
    public function set(string $option, $value): self
    {
        $this->options[$option] = $value;

        return $this;
    }

    /**
     * @param string $option
     *
     * @return self
     */
    public function del(string $option): self
    {
        unset($this->options[$option]);

        return $this;
    }
}
