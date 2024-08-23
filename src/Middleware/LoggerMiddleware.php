<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Middleware;

use Onliner\CommandBus\Context;
use Onliner\CommandBus\Middleware;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Throwable;

final class LoggerMiddleware implements Middleware
{
    public function __construct(
        private LoggerInterface $logger,
        private string $level = LogLevel::ERROR,
    ) {}

    public function call(object $message, Context $context, callable $next): void
    {
        try {
            $next($message, $context);
        } catch (Throwable $error) {
            $this->logger->log($this->level, $error->getMessage(), [
                'type' => get_class($error),
                'file' => $error->getFile(),
                'line' => $error->getLine(),
            ]);

            throw $error;
        }
    }
}
