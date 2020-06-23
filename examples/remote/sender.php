<?php

declare(strict_types=1);

$dispatcher = require __DIR__ . '/dispatcher.php';
$dispatcher->dispatch(new SendEmail('example@mail.com', 'Onliner', 'Hello world!'));
