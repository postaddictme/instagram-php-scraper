<?php

namespace InstagramScraper\Exception;

class InstagramException extends \Exception
{
    protected $responseBody;
    
    public function __construct($message = "", $code = 500, $responseBody = "", $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->responseBody = $responseBody;
    }
    
    public function getResponseBody()
    {
        return $this->responseBody;
    }

    public function getHttpCode()
    {
        return $this->code;
    }
}