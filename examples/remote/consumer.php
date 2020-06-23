<?php

declare(strict_types=1);

use Bunny\Client;
use Onliner\CommandBus\Remote\Consumer;

$dispatcher = require __DIR__ . '/dispatcher.php';

$project = 'mailer';
$client  = new Client();

// SETUP RABBIT
$client->connect();

$channel = $client->channel();
$channel->exchangeDeclare($project, 'topic', false, true);

$consumer = new Consumer($project, $client);
$consumer->subscribe(SendEmail::class);
$consumer->run($dispatcher);
