<?php
declare(strict_types = 1);
/**
 * This file is part of YAPEPBase.
 *
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */
namespace YapepBase\Debugger\Item;

/**
 * Interface for items that can return a location ID for their source.
 */
interface ISourceLocatable
{
    /**
     * Returns the location ID for the item's source in file @ line format.
     *
     * @return string
     */
    public function getLocationId();
}
