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
 * Storage debug item.
 */
class StorageItem extends QueryItemAbstract
{
    /** Set storage method. */
    const METHOD_SET = 'set';
    /** Get storage method. */
    const METHOD_GET = 'get';
    /** Delete storage method. */
    const METHOD_DELETE = 'delete';
    /** Clear storage method. */
    const METHOD_CLEAR = 'clear';

    /**
     * Returns the item's type.
     *
     * The type should be unique for the debug item.
     *
     * @return string
     */
    public function getType()
    {
        return self::DEBUG_ITEM_STORAGE;
    }
}
