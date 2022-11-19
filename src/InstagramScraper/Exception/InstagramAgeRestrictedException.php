<?php

namespace InstagramScraper\Exception;

class InstagramAgeRestrictedException extends InstagramException
{
    public function __construct($message = "", $code = 403, $responseBody = "", $previous = null)
    {
        parent::__construct($message, $code, $responseBody, $previous);
    }
}
