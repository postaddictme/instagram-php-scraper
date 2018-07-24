<?php

namespace InstagramScraper\Exception;

class InstagramAuthException extends \Exception
{
    public function __construct($message = "", $code = 401, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}