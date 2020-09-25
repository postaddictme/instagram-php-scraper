<?php

namespace InstagramScraper\Exception;

class InstagramRateLimitException extends InstagramException
{
    public function __construct($message = "", $code = 429, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
