<?php

namespace InstagramScraper\Exception;

class InstagramAuthException extends InstagramException
{
    public function __construct($message = "", $code = 401, $responseBody = "", $previous = null)
    {
        parent::__construct($message, $code, $responseBody, $previous);
    }
}