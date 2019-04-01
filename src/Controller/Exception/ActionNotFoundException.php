<?php
declare(strict_types=1);

namespace YapepBase\Controller\Exception;

class ActionNotFoundException extends Exception
{
    public function __construct(string $controller, string $action)
    {
        $message = $action . ' not found in ' . $controller;
        parent::__construct($message);
    }
}
