<?php

declare(strict_types=1);

namespace Onliner\CommandBus;

use Onliner\CommandBus\Message\DeferredIterator;

final class Context
{
    public const OPTION_LOCAL = 'local';

    /**
     * @param Dispatcher           $dispatcher
     * @param DeferredIterator     $deferred
     * @param array<string, mixed> $options
     */
    public function __construct(
        private Dispatcher $dispatcher,
        private DeferredIterator $deferred,
        private array $options = []
    ) {
    }

    /**
     * @param object               $message
     * @param array<string, mixed> $options
     *
     * @return void
     */
    public function dispatch(object $message, array $options = []): void
    {
        $this->dispatcher->dispatch($message, $options);
    }

    /**
     * @param object               $message
     * @param array<string, mixed> $options
     * @return void
     */
    public function execute(object $message, array $options = []): void
    {
        $this->dispatcher->dispatch($message, array_replace($options, [
            self::OPTION_LOCAL => true,
        ]));
    }

    /**
     * @param object               $message
     * @param array<string, mixed> $options
     *
     * @return self
     */
    public function defer(object $message, array $options = []): self
    {
        $this->deferred->append($message, $options);

        return $this;
    }

    /**
     * @return array<string, mixed>
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
    public function get(string $option, mixed $default = null): mixed
    {
        return $this->options[$option] ?? $default;
    }

    /**
     * @param string $option
     * @param mixed  $value
     *
     * @return self
     */
    public function set(string $option, mixed $value): self
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

    /**
     * @return bool
     */
    public function isLocal(): bool
    {
        return $this->has(self::OPTION_LOCAL);
    }
}
