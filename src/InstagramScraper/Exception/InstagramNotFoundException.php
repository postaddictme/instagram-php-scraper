<?php

namespace InstagramScraper\Exception;

use Exception;

class InstagramNotFoundException extends Exception
{
    public function __construct($message = "", $code = 404, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
