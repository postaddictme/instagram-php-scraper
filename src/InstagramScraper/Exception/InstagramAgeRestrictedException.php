<?php
declare(strict_types=1);

namespace InstagramScraper\Exception;

class InstagramAgeRestrictedException extends InstagramException
{
    public function __construct($message = "", $code = 403, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
