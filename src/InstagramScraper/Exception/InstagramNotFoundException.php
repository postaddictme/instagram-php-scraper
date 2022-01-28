<?php

namespace InstagramScraper\Exception;

class InstagramNotFoundException extends InstagramException
{
    public function __construct($message = "", $code = 404, $responseBody = "", $previous = null)
    {
        parent::__construct($message, $code, $responseBody, $previous);
    }
}