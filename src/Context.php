<?php

declare(strict_types=1);

namespace Onliner\CommandBus;

interface Context
{
    /**
     * @param object       $message
     * @param array<mixed> $options
     *
     * @return void
     */
    public function dispatch(object $message, array $options = []): void;

    /**
     * @return array<mixed>
     */
    public function all(): array;

    /**
     * @param string $option
     *
     * @return bool
     */
    public function has(string $option): bool;

    /**
     * @param string $option
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get(string $option, $default = null);

    /**
     * @param string $option
     * @param mixed  $value
     *
     * @return self
     */
    public function set(string $option, $value): self;

    /**
     * @param string $option
     *
     * @return self
     */
    public function del(string $option): self;
}
