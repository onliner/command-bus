<?php

declare(strict_types=1);

class SendEmail
{
    /**
     * @var string
     */
    public $to;

    /**
     * @var string
     */
    public $subject;

    /**
     * @var string
     */
    public $content;

    /**
     * @param string $to
     * @param string $subject
     * @param string $content
     */
    public function __construct(string $to, string $subject, string $content)
    {
        $this->to      = $to;
        $this->subject = $subject;
        $this->content = $content;
    }
}
