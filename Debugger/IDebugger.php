<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Debugger
 * @author       Zsolt Szeberenyi <szeber@yapep.org>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */


namespace YapepBase\Debugger;

/**
 * Debugger interface
 *
 * @package    YapepBase
 * @subpackage Debugger
 */
interface IDebugger {

    /** Type of the query: Database */
    const QUERY_TYPE_DB = 'db';
    /** Type of the query: Cache */
    const QUERY_TYPE_CACHE = 'cache';
    /** Type of the query: curl */
    const QUERY_TYPE_CURL = 'curl';

    /**
     * Stores a timing with the given name.
     *
     * @param string $name   The name of the timing.
     *
     * @return void
     */
	public function addClockMilestone($name);

    /**
     * Stores a memory usage with the given name.
     *
     * @param string $name   The name of the measure.
     *
     * @return void
     */
	public function addMemoryUsageMilestone($name);

    /**
     * Stores a message.
     *
     * @param mixed $message   The message that should be stored.
     *
     * @return void
     */
	public function logInfo($message);

    /**
     * Logs and error.
     *
     * @param int    $code      The code of the error.
     * @param string $message   Error message.
     * @param string $file      The name of the file, where the error occured.
     * @param int    $line      The number of the line where the error occured.
     * @param array  $context   The variables from the actual context.
     *
     * @return void
     */
	public function logError($code, $message, $file = '', $line = 0, array $context = array());

    /**
     * Logs the given query.
     *
     * @param string $type     The type of the query {@uses IDebugger::QUERY_TYPE_*}.
     * @param string $query    The query string.
     * @param mixed  $params   The params used by the query.
     *
     * @return int   The id of the query, which can be used to measure the execution time of it.
     */
    public function logQuery($type, $query, $params = null);

    /**
     * Logs the timing of the given query.
     *
     * @param string $type            The type of the query {@uses IDebugger::QUERY_TYPE_*}.
     * @param int    $queryId         The id of the query.
     * @param float  $executionTime   The execution time of the query.
     * @param mixed  $params          The params what used by the query.
     *
     * @return void
     */
    public function logQueryExecutionTime($type, $queryId, $executionTime, $params = null) ;
}