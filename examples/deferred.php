<?php

declare(strict_types=1);

use Onliner\CommandBus\Builder;
use Onliner\CommandBus\Context;

require __DIR__ . '/../vendor/autoload.php';

class Task
{
    public function __construct(
        public string $task,
    ) {}
}

class ResultMessage
{
    public function __construct(
        public string $message,
    ) {}
}

class TaskHandler
{
    public function __invoke(Task $command, Context $context)
    {
        echo "Task: {$command->task} processing start\n" ;

        $context->defer(new ResultMessage('success'));

        echo "Task: {$command->task} processing end\n" ;
    }
}

class NotifyResultHandler
{
    public function __invoke(ResultMessage $command, Context $context)
    {
        echo "Result: {$command->message}\n" ;
    }
}

$dispatcher = (new Builder())
    ->handle(Task::class, new TaskHandler())
    ->handle(ResultMessage::class, new NotifyResultHandler())
    ->build();

$dispatcher->dispatch(new Task('build report'));

/**
 * Result:
 *
 * Task: build report processing start
 * Task: build report processing end
 * Result: success
 */
