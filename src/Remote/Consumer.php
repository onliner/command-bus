<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote;

interface Consumer
{
    /**
     * @param callable             $handler
     * @param array<string, mixed> $options
     *
     * @return void
     */
    public function run(callable $handler, array $options = []): void;

    /**
     * @return void
     */
    public function stop(): void;
}
