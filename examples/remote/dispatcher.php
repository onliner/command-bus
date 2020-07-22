<?php

declare(strict_types=1);

use Bunny\Client;
use Onliner\CommandBus\Builder;
use Onliner\CommandBus\Remote\Bunny\BunnyTransport;
use Onliner\CommandBus\Remote\Bunny\ExchangeOptions;
use Onliner\CommandBus\Remote\RemoteExtension;

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/messages.php';

$transport = BunnyTransport::create('amqp://guest:guest@localhost:5672', new ExchangeOptions('mailer'));

$remote = new RemoteExtension($transport);
$remote->route(SendEmail::class, $transport->exchange());

return (new Builder())
    ->handle(SendEmail::class, function (SendEmail $command) {
        echo 'MAILTO: ',  $command->to, \PHP_EOL;
        echo 'SUBJECT: ', $command->subject, \PHP_EOL;
        echo 'CONTENT: ', $command->content, \PHP_EOL;
    })
    ->use($remote)
    ->build();
