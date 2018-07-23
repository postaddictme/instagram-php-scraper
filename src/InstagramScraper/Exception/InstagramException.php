<?php

namespace InstagramScraper\Exception;

use Throwable;

class InstagramException extends \Exception
{
    public function __construct($message = "", $code = 500, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}