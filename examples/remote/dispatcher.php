<?php

declare(strict_types=1);

use Bunny\Client;
use Onliner\CommandBus\Builder;
use Onliner\CommandBus\Remote\RemoteExtension;
use Onliner\CommandBus\Remote\Transport;

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/messages.php';

$remote = new RemoteExtension(new Transport\BunnyTransport(new Client()));
$remote->route(SendEmail::class, 'mailer');

return (new Builder())
    ->handle(SendEmail::class, function (SendEmail $command) {
        echo 'MAILTO: ',  $command->to, \PHP_EOL;
        echo 'SUBJECT: ', $command->subject, \PHP_EOL;
        echo 'CONTENT: ', $command->content, \PHP_EOL;
    })
    ->use($remote)
    ->build();
