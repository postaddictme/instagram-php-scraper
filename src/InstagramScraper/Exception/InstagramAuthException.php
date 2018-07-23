<?php

namespace InstagramScraper\Exception;

use Throwable;

class InstagramAuthException extends \Exception
{
    public function __construct($message = "", $code = 401, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}