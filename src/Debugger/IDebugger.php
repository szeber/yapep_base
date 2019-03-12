<?php
declare(strict_types = 1);
/**
 * This file is part of YAPEPBase.
 *
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */
namespace YapepBase\Debugger;

use YapepBase\Debugger\Item\IDebugItem;

/**
 * Debugger interface
 */
interface IDebugger
{
    /** Type of the query: Database */
    const QUERY_TYPE_DB = 'db';
    /** Type of the query: Cache */
    const QUERY_TYPE_CACHE = 'cache';
    /** Type of the query: CURL */
    const QUERY_TYPE_CURL = 'curl';

    /** Type of the counter: Database */
    const COUNTER_TYPE_DB = 'db';
    /** Type of the counter: Cache */
    const COUNTER_TYPE_CACHE = 'cache';
    /** Type of the counter: CURL */
    const COUNTER_TYPE_CURL = 'curl';
    /** Type of the counter: Error */
    const COUNTER_TYPE_ERROR = 'error';

    /**
     * Returns the time when the request was started as a float timestamp (microtime).
     *
     * @return float
     */
    public function getStartTime();

    /**
     * Adds a new debug item to the debugger.
     *
     * @param \YapepBase\Debugger\Item\IDebugItem $item   The debug item.
     *
     * @return void
     */
    public function addItem(IDebugItem $item);

    /**
     * Handles the shut down event.
     *
     * This method should called in case of shutdown(for example fatal error).
     *
     * @return mixed
     */
    public function handleShutdown();
}
