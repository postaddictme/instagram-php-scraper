<?php

namespace InstagramScraper\Exception;

class InstagramChallengeRecaptchaException extends InstagramException
{
    public function __construct($message = "", $code = 400, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
