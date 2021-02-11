<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote;

use Onliner\CommandBus\Dispatcher;

interface Consumer
{
    /**
     * @param Dispatcher           $dispatcher
     * @param array<string, mixed> $options
     *
     * @return void
     */
    public function run(Dispatcher $dispatcher, array $options = []): void;

    /**
     * @return void
     */
    public function stop(): void;
}
