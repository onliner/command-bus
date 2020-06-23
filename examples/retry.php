<?php

declare(strict_types=1);

use Onliner\CommandBus\Builder;
use Onliner\CommandBus\Context;
use Onliner\CommandBus\Retry\RetryExtension;
use Onliner\CommandBus\Retry\SimplePolicy;

require __DIR__ . '/../vendor/autoload.php';

class MaybeFail
{
}

$retry = new RetryExtension();
$retry->policy(MaybeFail::class, new SimplePolicy(3));

$dispatcher = (new Builder())
    ->handle(MaybeFail::class, function (MaybeFail $command, Context $context) {
        $attempts = $context->get('attempt', 1);

        if ($attempts < 3) {
            echo 'Fail ' , $attempts , ' times', \PHP_EOL;

            throw new LogicException();
        }

        echo 'Executed!', \PHP_EOL;
    })
    ->use($retry)
    ->build();

$dispatcher->dispatch(new MaybeFail());
