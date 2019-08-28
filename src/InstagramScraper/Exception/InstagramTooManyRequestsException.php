<?php

namespace InstagramScraper\Exception;

class InstagramTooManyRequestsException extends \Exception
{
    public function __construct($message = "", $code = 429, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
