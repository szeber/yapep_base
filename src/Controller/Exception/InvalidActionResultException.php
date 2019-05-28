<?php
declare(strict_types=1);

namespace YapepBase\Controller\Exception;

class InvalidActionResultException extends Exception
{
    public function __construct(string $controller, string $action)
    {
        $message = 'Result of the action (' . $controller . '/' . $action . ') is not an instance of ViewAbstract or string';
        parent::__construct($message);
    }
}
