<?php

namespace InstagramScraper\Exception;

use Exception;

class InstagramException extends Exception
{
    public function __construct($message = "", $code = 500, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
