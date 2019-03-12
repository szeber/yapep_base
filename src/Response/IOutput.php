<?php
declare(strict_types = 1);
/**
 * This file is part of YAPEPBase.
 *
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */
namespace YapepBase\Response;

/**
 * Classes implementing this interface handle the raw output to the browser,
 * etc. It has been implemented to separate the PHP-dependant code parts.
 */
interface IOutput
{
    /**
     * Outputs all parameters.
     *
     * @return void
     */
    public function out();
}
