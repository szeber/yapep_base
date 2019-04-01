<?php
declare(strict_types=1);

namespace YapepBase\Controller\Exception;

use YapepBase\Request\IRequest;

class IncompatibleRequestException extends Exception
{
    public function __construct(IRequest $request, string $expectedRequestType)
    {
        $message = 'Only ' . $expectedRequestType . ' request type is accepted, ' . get_class($request) . ' given instead!';
        parent::__construct($message);
    }
}
