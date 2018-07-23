<?php

namespace InstagramScraper\Exception;

use Throwable;

class InstagramNotFoundException extends \Exception
{
    public function __construct($message = "", $code = 404, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}