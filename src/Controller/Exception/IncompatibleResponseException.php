<?php
declare(strict_types=1);

namespace YapepBase\Controller\Exception;

use YapepBase\Response\IResponse;

class IncompatibleResponseException extends Exception
{
    public function __construct(IResponse $response, string $expectedResponseType)
    {
        $message = 'Only ' . $expectedResponseType . ' response type is accepted, ' . get_class($response) . ' given instead!';
        parent::__construct($message);
    }
}
