<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote;

use Onliner\CommandBus\Dispatcher;

interface Consumer
{
    /**
     * @param string     $queue
     * @param Dispatcher $dispatcher
     *
     * @return void
     */
    public function run(string $queue, Dispatcher $dispatcher): void;

    /**
     * @return void
     */
    public function stop(): void;
}
