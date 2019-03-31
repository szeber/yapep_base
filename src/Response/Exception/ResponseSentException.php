<?php
declare(strict_types=1);

namespace YapepBase\Response\Exception;

class ResponseSentException extends Exception
{
    public function __construct()
    {
        parent::__construct('Response already sent!');
    }
}
