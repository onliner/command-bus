<?php

declare(strict_types=1);

class SendEmail
{
    public function __construct(
        public string $to,
        public string $subject,
        public string $content,
    ) {}
}
