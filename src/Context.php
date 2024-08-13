<?php

declare(strict_types=1);

namespace Onliner\CommandBus;

use Onliner\CommandBus\Message\DeferredIterator;

final class Context
{
    private const OPTION_LOCAL = 'local';

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        private Dispatcher $dispatcher,
        private DeferredIterator $deferred,
        private array $options = [],
    ) {}

    /**
     * @param array<string, mixed> $options
     */
    public function dispatch(object $message, array $options = []): void
    {
        $this->dispatcher->dispatch($message, $options);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function execute(object $message, array $options = []): void
    {
        $this->dispatcher->dispatch($message, array_replace($options, [
            self::OPTION_LOCAL => true,
        ]));
    }

    /**
     * @param array<string, mixed> $options
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

    public function has(string $option): bool
    {
        return array_key_exists($option, $this->options);
    }

    public function get(string $option, mixed $default = null): mixed
    {
        return $this->options[$option] ?? $default;
    }

    public function set(string $option, mixed $value): self
    {
        $this->options[$option] = $value;

        return $this;
    }

    public function del(string $option): self
    {
        unset($this->options[$option]);

        return $this;
    }

    public function isLocal(): bool
    {
        return $this->has(self::OPTION_LOCAL);
    }
}
