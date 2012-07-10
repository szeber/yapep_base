<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Debugger
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Debugger;

use YapepBase\Application;
use YapepBase\Response\HttpResponse;
use YapepBase\Mime\MimeType;
use YapepBase\View\Template\ConsoleDebuggerTemplate;
use YapepBase\View\ViewDo;

/**
 * Simple floating console debugger renderer, which will be injected into the generated HTML output,
 * so the developer can see useful information about the generation process.
 *
 * @package    YapepBase
 * @subpackage Debugger
 */
class ConsoleDebuggerRenderer implements IDebuggerRenderer {

	/**
	 * Renders the output.
	 *
	 * @param float $startTime       The unix timestamp of the application start with microseconds.
	 * @param float $runTime         The number of seconds with microseconds, the application run for.
	 * @param int   $currentMemory   The current memory usage in bytes.
	 * @param int   $peakMemory      The peak memory usage in bytes.
	 * @param array $times           Array with the timing milestones.
	 * @param array $memoryUsages    Array with the memory usage milestones.
	 * @param array $info            Array with the logged info items.
	 * @param array $errors          Array with any errors that occured.
	 * @param array $queries         Array with all queries that have run (including timing information if available).
	 * @param array $queryTimes      Array with the timing totals for each query type.
	 * @param array $counters        Array with the location counters.
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
		array $times,
		array $memoryUsages,
		array $info,
		array $errors,
		array $queries,
		array $queryTimes,
		array $counters,
		array $serverParams,
		array $postParams,
		array $getParams,
		array $cookieParams,
		array $sessionParams
	) {
		$response = Application::getInstance()->getResponse();
		/** @var \YapepBase\Response\HttpResponse $response */
		if (!($response instanceof HttpResponse)
			|| !in_array($response->getContentType(), array(MimeType::HTML, MimeType::XHTML))
		) {
			// This renderer only works for HTTP transport and HTML content type
			return;
		}

		$viewDo = new ViewDo(MimeType::HTML);

		$viewDo->set(array(
			'runTime'       => $runTime,
			'peakMemory'    => $peakMemory,
			'times'         => $times,
			'memoryUsages'  => $memoryUsages,
			'info'          => $info,
			'errors'        => $errors,
			'queries'       => $queries,
			'queryTimes'    => $queryTimes,
			'counters'      => $counters,
			'serverParams'  => $serverParams,
			'postParams'    => $postParams,
			'getParams'     => $getParams,
			'cookieParams'  => $cookieParams,
			'sessionParams' => $sessionParams
		));

		$template = new ConsoleDebuggerTemplate(
			$viewDo,
			'runTime',
			'peakMemory',
			'times',
			'memoryUsages',
			'info',
			'errors',
			'queries',
			'queryTimes',
			'counters',
			'serverParams',
			'postParams',
			'getParams',
			'cookieParams',
			'sessionParams');

		$template->render();
	}

}