<?php


namespace InstagramScraper\Exception;


class InstagramNotImplementedException extends \Exception
{
    public function __construct($message = "", $code = 501, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}