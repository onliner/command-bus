<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote;

use Onliner\CommandBus\Dispatcher;

interface Consumer
{
    /**
     * @param array<string, mixed> $options
     */
    public function run(Dispatcher $dispatcher, array $options = []): void;
    public function stop(): void;
}
