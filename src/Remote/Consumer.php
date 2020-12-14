<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote;

use Onliner\CommandBus\Dispatcher;

interface Consumer
{
    /**
     * @param Dispatcher $dispatcher
     */
    public function run(Dispatcher $dispatcher): void;

    /**
     * @return void
     */
    public function stop(): void;
}
