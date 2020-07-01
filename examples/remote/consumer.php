<?php

declare(strict_types=1);

use Bunny\Client;
use Onliner\CommandBus\Remote\Bunny\BunnyTransport;

$dispatcher = require __DIR__ . '/dispatcher.php';

$transport = new BunnyTransport('mailer', new Client());

$consumer = $transport->consume();
$consumer->run('#', $dispatcher);





//
//// SETUP RABBIT
//$client->connect();
//
//$channel = $client->channel();
//
