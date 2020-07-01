<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Remote;

use Onliner\CommandBus\Dispatcher;

interface Consumer
{
    public function run(string $queue, Dispatcher $dispatcher);
}
