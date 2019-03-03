<?php
declare(strict_types=1);

namespace YapepBase\Router;

use YapepBase\Router\DataObject\Route;

interface IRouteGetter
{
    /**
     * @return Route[]
     */
    public function getRoutesByControllerAction(): array;
}
