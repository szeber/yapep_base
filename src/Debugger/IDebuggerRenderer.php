<?php
declare(strict_types = 1);
/**
 * This file is part of YAPEPBase.
 *
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */
namespace YapepBase\Debugger;

/**
 * Interface for implementing debugger renderers.
 *
 * The render method's array have the following structure:
 * <ul>
 *     <li>
 *         <b>$times</b> contains associative arrays with the following keys:
 *         <ul>
 *             <li>name: name of the milestone</li>
 *             <li>logged: unix timestamp with microseconds of the milestone logging time</li>
 *             <li>elapsed: elapsed time since the application start in seconds, with microsecond precision</li>
 *         </ul>
 *     </li>
 *     <li>
 *         <b>$memoryUsages</b> contains associative arrays with the following keys:
 *         <ul>
 *             <li>name: name of the milestone</li>
 *             <li>current: the current memory usage at the moment of logging</li>
 *             <li>peak: the peak memory usage until the milestone</li>
 *         </ul>
 *     </li>
 *     <li>
 *         <b>$info</b> contains associative arrays with the following keys:
 *         <ul>
 *             <li>message: the logged message (may be of any type)</li>
 *             <li>file: the file of the logging</li>
 *             <li>line: the line of the logging</li>
 *         </ul>
 *     </li>
 *     <li>
 *         <b>$errors</b> contains associative arrays with the following keys:
 *         <ul>
 *             <li>code: The error code</li>
 *             <li>message: The error message</li>
 *             <li>file: The file of the error</li>
 *             <li>line: Line of the error</li>
 *             <li>context: The variables from the actual context.</li>
 *             <li>trace: The backtrace for the error.</li>
 *             <li>id: The id of the error.</li>
 *             <li>source: The source code around the error's origin.</li>
 *         </ul>
 *     </li>
 *     <li>
 *         <b>$queries</b> contains keys for each query type {@uses \YapepBase\Debugger\IDebugger::QUERY_TYPE_*},
 *            which contain associative arrays with the following keys:
 *         <ul>
 *             <li>file: The source file of the query</li>
 *             <li>line: The source line of the query</li>
 *             <li>query: The query string</li>
 *             <li>params: Query parameters</li>
 *             <li>runtime: The time, the query took to complete, if it's available. NULL otherwise.</li>
 *         </ul>
 *     </li>
 *     <li>
 *         <b>$queryTimes</b> contains keys for each query type {@uses \YapepBase\Debugger\IDebugger::COUNTER_TYPE_*},
 *            which contain associative arrays with the source location of each counter (in &lt;file&gt;@&lt;line&gt;
 *            format), and the count. The counters can be used for example to visualize if a query has been run in a
 *            loop, or if the same line causes a lot of errors.
 *     </li>
 *
 * </ul>
 */
interface IDebuggerRenderer
{
    /**
     * Renders the output.
     *
     * @param float $startTime       The unix timestamp of the application start with microseconds.
     * @param float $runTime         The number of seconds with microseconds, the application run for.
     * @param int   $currentMemory   The current memory usage in bytes.
     * @param int   $peakMemory      The peak memory usage in bytes.
     * @param array $items           2 dimensional array of the debug items, where the 1st dimension's key is the type.
     * @param array $serverParams    Params of the $_SERVER superglobal.
     * @param array $postParams      Params what received through post method.
     * @param array $getParams       Params what received through get method.
     * @param array $cookieParams    Params what received through cookies.
     * @param array $sessionParams   Data what stored in the session.
     *
     * @return void
     */
    public function render(
        $startTime,
        $runTime,
        $currentMemory,
        $peakMemory,
        array $items,
        array $serverParams,
        array $postParams,
        array $getParams,
        array $cookieParams,
        array $sessionParams
    );
}
