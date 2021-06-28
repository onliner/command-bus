<?php

declare(strict_types=1);

namespace Onliner\CommandBus\Middleware;

use Onliner\CommandBus\Context;
use Onliner\CommandBus\Helper\MessageDumper;
use Onliner\CommandBus\Middleware;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Throwable;

final class LoggerMiddleware implements Middleware
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $level;

    /**
     * @param LoggerInterface $logger
     * @param string          $level
     */
    public function __construct(LoggerInterface $logger, string $level = LogLevel::ERROR)
    {
        $this->logger = $logger;
        $this->level  = $level;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Throwable
     */
    public function call(object $message, Context $context, callable $next): void
    {
        try {
            $next($message, $context);
        } catch (Throwable $error) {
            $this->logger->log($this->level, $error->getMessage(), [
                'options' => $context->all(),
                'message' => get_class($message),
                'payload' => MessageDumper::dump($message),
            ]);

            throw $error;
        }
    }
}
