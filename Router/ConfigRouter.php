<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Router
 * @author       Zsolt Szeberenyi <szeber@yapep.org>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */


namespace YapepBase\Router;

/**
 * ConfigRouter class.
 *
 * Routes a request based on an array stored in a config variable.
 *
 * @package    YapepBase
 * @subpackage Router
 */
use YapepBase\Config;

use YapepBase\Request\IRequest;

class ConfigRouter extends ArrayRouter {

    public function __construct(IRequest $request, $configName) {
        $routes = Config::getInstance()->get($configName, false);
        if (!is_array($routes)) {
            // TODO throw exception
        }
        parent::__construct($request, $routes);
    }

}