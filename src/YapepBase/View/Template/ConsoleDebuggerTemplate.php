<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   View\Template
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\View\Template;


use Exception;

use YapepBase\Application;
use YapepBase\Debugger\Item\MemoryUsageItem;
use YapepBase\Debugger\Item\MessageItem;
use YapepBase\Debugger\Item\StorageItem;
use YapepBase\Debugger\Item\CurlRequestItem;
use YapepBase\Debugger\Item\ErrorItem;
use YapepBase\Debugger\Item\IDebugItem;
use YapepBase\Debugger\Item\SqlQueryItem;
use YapepBase\Debugger\Item\TimeItem;
use YapepBase\ErrorHandler\ErrorHandlerHelper;
use YapepBase\Helper\FileHelper;
use YapepBase\View\TemplateAbstract;
use YapepBase\View\ViewDo;

/**
 * Template for the console debugger's output
 *
 * @package    YapepBase
 * @subpackage View\Template
 */
class ConsoleDebuggerTemplate extends TemplateAbstract {

	/**
	 * The time the request was started.
	 *
	 * @var int
	 */
	protected $startTime;

	/**
	 * The run time.
	 *
	 * @var float
	 */
	protected $runTime;

	/**
	 * Peak memory usage in bytes.
	 *
	 * @var int
	 */
	protected $peakMemory;

	/**
	 * Time debug items.
	 *
	 * These items have NOT been escaped by the view DO, so all values must be separately escaped.
	 *
	 * @var array
	 */
	protected $items;

	/**
	 * Content of the $_SERVER superglobal array.
	 *
	 * @var array
	 */
	protected $serverParams;

	/**
	 * Params received through post method.
	 *
	 * @var array
	 */
	protected $postParams;

	/**
	 * Params received through get method.
	 *
	 * @var array
	 */
	protected $getParams;

	/**
	 * Params received through cookies.
	 *
	 * @var array
	 */
	protected $cookieParams;

	/**
	 * Params stored in the session.
	 *
	 * @var array
	 */
	protected $sessionParams;

	/**
	 * Constructor
	 *
	 * @param ViewDo $viewDo           The ViewDo instance to use.
	 * @param string $_startTime       Key to the start timestamp.
	 * @param string $_runTime         Key of the run time.
	 * @param string $_peakMemory      Key of the peak memory usage.
	 * @param string $_items           Key to the debug items.
	 * @param string $_serverParams    Key to the $_SERVER super global array.
	 * @param string $_postParams      Key to the params received through post mthod.
	 * @param string $_getParams       Key to the params received through get method.
	 * @param string $_cookieParams    Key to the params received through cookies.
	 * @param string $_sessionParams   Key to the Session data.
	 */
	public function __construct(
		ViewDo $viewDo, $_startTime, $_runTime, $_peakMemory, $_items, $_serverParams, $_postParams, $_getParams,
		$_cookieParams, $_sessionParams
	) {
		$this->setViewDo($viewDo);

		$this->startTime     = $this->get($_startTime);
		$this->runTime       = $this->get($_runTime);
		$this->peakMemory    = $this->get($_peakMemory);
		$this->items         = $this->get($_items, true);
		$this->serverParams  = $this->get($_serverParams);
		$this->postParams    = $this->get($_postParams);
		$this->getParams     = $this->get($_getParams);
		$this->cookieParams  = $this->get($_cookieParams);
		$this->sessionParams = $this->get($_sessionParams);
	}

	/**
	 * Shapes the given SQL query in a displayable form.
	 *
	 * @param string $query    The queryString.
	 * @param array  $params   The parameters to the given query.
	 *
	 * @return string   The displayable query.
	 */
	protected function formatSqlQuery($query, array $params) {
		// Removing the indentations
		$matches = array();
		preg_match('/^\s*/', $query, $matches);
		if (isset($matches[0])) {
			$query = str_replace($matches[0], "\n", trim($query));
		}
		$query = preg_replace('/\t/', '    ', $query);

		// Replacing the parameters of the query
		foreach ($params as $paramLabel => $paramValue) {
			if (is_int($paramLabel)) {
				$firstParamPosition = strpos($query, '?');
				if ($firstParamPosition !== false) {
					$query = substr_replace($query, $paramValue, $firstParamPosition, 1);
				}
			}
			else {
				$query = str_replace(':_' . $paramLabel, $paramValue, $query);
			}
		}

		return $query;
	}

	/**
	 * Creates a displayable HTML from the given error, and stores it.
	 *
	 * @param \YapepBase\Debugger\Item\ErrorItem $error   The error item.
	 *
	 * @return string
	 */
	public function getFormattedError(ErrorItem $error) {
		static $errorHandlerHelper;

		if (empty($errorHandlerHelper)) {
			$errorHandlerHelper = new ErrorHandlerHelper;
		}

		$errorData = $error->getData();
		$file = $this->viewDo->escape($errorData[ErrorItem::FIELD_FILE]);
		$line = $this->viewDo->escape($errorData[ErrorItem::FIELD_LINE]);

		$id = $this->viewDo->escape($errorData[ErrorItem::LOCAL_FIELD_ID]);
		$message = '[' . $this->viewDo->escape(
			$errorHandlerHelper->getPhpErrorLevelDescription($errorData[ErrorItem::LOCAL_FIELD_CODE]))
			. '] ' . $this->viewDo->escape($errorData[ErrorItem::LOCAL_FIELD_MESSAGE]);
		$source = FileHelper::getEnvironment($file, $line, 5);
		$firstLine = key($source);

		$errorHtml = '
			<div class="yapep-debug-error-item">
				<p class="yapep-debug-clickable" onclick="Yapep.toggle(\'error-' . $id . '\'); return false;">
					' . $message . '<br/>
					in <var>' . $file . '</var>, <u>line ' . $line . '</u>
				</p>
				<div class="yapep-debug-container" id="yapep-debug-error-' . $id . '">
					<h3>Source code</h3>
					<ol start="' . $firstLine . '" class="yapep-debug-code">
		';
		foreach ($source as $lineNumber => $codeLine) {
			if ($codeLine === '') {
				$codeLine = ' ';
			}
			$codeLine = str_replace('&lt;?php&nbsp;', '', highlight_string('<?php '.$codeLine, true));
			if ($line > $lineNumber) {
				foreach (
					$errorData[ErrorItem::LOCAL_FIELD_CONTEXT] + $errorData[ErrorItem::LOCAL_FIELD_TRACE]
						as $varName => $value
				) {
					if (is_scalar($value) || is_array($value)) {
						$tooltip = $this->viewDo->escape(gettype($value) . ': ' . print_r($value, true));
					}
					elseif ($value instanceof Exception) {
						$tooltip = $this->viewDo->escape(get_class($value) . ': ' . $value->getMessage());
					}
					elseif (is_object($value) && method_exists($value, '__toString')) {
						$tooltip = $this->viewDo->escape(get_class($value) . ': ' . $value->__toString());
					}
					else {
						$tooltip = $this->viewDo->escape(strtoupper(gettype($value)));
					}

					$codeLine = preg_replace('#(?<!::)\$' . $varName . '\b#',
						'<var title="' . htmlspecialchars($tooltip) . '">' . '$' . $varName . '</var>', $codeLine);
				}
			}
			$errorHtml .= '<li class="'.($lineNumber % 2 ? 'odd ' : '')
				. ($lineNumber == $line ? 'yapep-debug-code-highlight' : '') . '">' . $codeLine . '</li>';
		}
		$errorHtml .= '</ol>';

		if ($errorData[ErrorItem::LOCAL_FIELD_CODE] === ErrorHandlerHelper::E_EXCEPTION) {
			$errorHtml .= '<h3>Debug trace</h3>'
				. '<pre id="yapep-debug-error-trace-' . $id . '">'
				. highlight_string(print_r($errorData[ErrorItem::LOCAL_FIELD_TRACE], true), true)
				. '</pre>';
		}

		$errorHtml .= '</div>';
		$errorHtml .= '</div>';

		return $errorHtml;
	}

	/**
	 * Does the actual rendering.
	 *
	 * @return void
	 */
	protected function renderContent() {
		$messages = array();
		$errors = array();
		$sqlQueries = array();
		$sqlQueryTimes = 0;
		$cacheRequests = array();
		$cacheTimes = 0;
		$storageRequests = array();
		$storageTimes = 0;
		$curlRequests = array();
		$curlTimes = 0;
		$timeMilestones = array();
		$memoryMilestones = array();
		$resources = array();

		$cacheBackendTypes = array('memcache', 'memcached', 'dummy');
		foreach ($this->items as $type => $items) {
			switch ($type) {
				case IDebugItem::DEBUG_ITEM_MESSAGE:
					$messages = $items;
					break;

				case IDebugItem::DEBUG_ITEM_ERROR:
					$errors = $items;
					break;

				case IDebugItem::DEBUG_ITEM_SQL_QUERY:
					$sqlQueries = $items;
					foreach ($items as $item) {
						/** @var \YapepBase\Debugger\Item\IDebugItem $item */
						$data = $item->getData();
						$sqlQueryTimes += $data[SqlQueryItem::LOCAL_FIELD_EXECUTION_TIME];
					}
					break;

				case IDebugItem::DEBUG_ITEM_STORAGE:
					$storageRequests = $items;
					foreach ($items as $item) {
						/** @var \YapepBase\Debugger\Item\IDebugItem $item */
						$data = $item->getData();
						$storageTimes += $data[StorageItem::LOCAL_FIELD_EXECUTION_TIME];
						if (in_array($data[StorageItem::LOCAL_FIELD_BACKEND_TYPE], $cacheBackendTypes)) {
							$cacheRequests[] = $item;
							$cacheTimes += $data[StorageItem::LOCAL_FIELD_EXECUTION_TIME];
						}
					}
					break;

				case IDebugItem::DEBUG_ITEM_CURL_REQUEST:
					$curlRequests = $items;
					foreach ($items as $item) {
						/** @var \YapepBase\Debugger\Item\IDebugItem $item */
						$data = $item->getData();
						$curlTimes += $data[CurlRequestItem::LOCAL_FIELD_EXECUTION_TIME];
					}
					break;

				case IDebugItem::DEBUG_ITEM_TIME_MILESTONE:
					$timeMilestones = $items;
					break;

				case IDebugItem::DEBUG_ITEM_MEMORY_USAGE_MILESTONE:
					$memoryMilestones = $items;
					break;

				default:
					$resources[$type] = $items;
					break;
			}
		}
// -------------------- HTML ------------------- ?>

<style type="text/css">
#yapep-debug {
	width:100%;
	position:absolute;
	right:0;
	top:0;
	font:12px/100% Arial,sans-serif;
	color:#000;
	text-align:left;
	z-index: 999;
}

#yapep-debug u {
	text-decoration: underline;
}

.yapep-debug-copyable {
	cursor: help;
}

.yapep-debug-clickable {
	cursor:pointer;
}

#yapep-debug-toolbar {
	z-index:10;
	position:absolute;
	right:0;
	top:0;
	background: #ec9;
	border-bottom:1px solid #888;
	border-left:1px solid #888;
}

#yapep-debug.yapep-debug-error #yapep-debug-toolbar {
	background: #f23;
}

#yapep-debug-toolbar ul {
	list-style:none;
	white-space:nowrap;
	margin:0;
	padding:0;
	float: left;
}

#yapep-debug-toolbar li {
	float:left;
	margin:0 5px;
}

#yapep-debug-toolbar a {
	text-decoration:none;
	color:#000;
}

#yapep-debug-toolbar span {
	background:no-repeat 2px 2px;
	display:block;
	padding:5px 2px 5px 22px;
}

#yapep-debug-toolbar .yapep-debug-log {
	background-image:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAIbSURBVDjLjVPPaxNREJ79Qena1EO6u/GQ9CiFouCp5FQQET0KQvBQbA/tqf+BCEXoyauCl7KFHkoOvYimUpToRTyISVtsliImpCwkLUGqxvzY3bfOvO2+bOgljx32vdn5Zr4336wUBAGUy+V7f96/3PVaDnjNKty17DkYbZ1KpVLppu/7n5nbnVDAh7NXK3Bn4/tIaFVV59R8Pm9ns9nV8aOClZhCbwDguu5QIGMMiGn8rGlamCSXy80ggxfMXAAFPPj9qXipkizLHBQtSZJEQsFg7KBgTZroZGEArWc7TSAchXIA4w+sPdQH1xAMDGQgeXD+4aNIQODZjHaRILT9Wpt/Q8wwA3X/rXVVD3glkQD3h7V/vGrA8Bvz0Rf2AK/F7zRQoY8qIAPn+TLczx/xRPF709nzPOFHayeTyfkBg29vrEkj5BkFPdlu4NtHugH4wYUSqNBaziQGE5hXifXgMVfh115RdHr90TUOIkPNBZtutwvVahUURZFlYuA4zmqzsAl/v24BFhQSRXJFDYvAlUoFUqkU+VmMwSLIyKC1W4ypwISRr9PpgG3bkMlkQNf1YRXkL6+thIlN8y9PIDGgygROp9NgGMZgqOIqEIPa0yV4sPeDgwlIne/1etBoNHhV0zTjExn+Cxh041bl3c8rSY0PCzWIgGQRCxpnSlKv1/m+3++HSaKGLV2fmp9OjN122u7JxnHrYNTf+T+76nzVPsi2lQAAAABJRU5ErkJggg==);
}

#yapep-debug-toolbar .yapep-debug-message {
	background-image:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAKcSURBVDjLpZPLa9RXHMU/d0ysZEwmMQqZiTaP0agoaKGJUiwIxU0hUjtUQaIuXHSVbRVc+R8ICj5WvrCldJquhVqalIbOohuZxjDVxDSP0RgzyST9zdzvvffrQkh8tBs9yy9fPhw45xhV5X1U8+Yhc3U0LcEdVxdOVq20OA0ooQjhpnfhzuDZTx6++m9edfDFlZGMtXKxI6HJnrZGGtauAWAhcgwVnnB/enkGo/25859l3wIcvpzP2EhuHNpWF9/dWs/UnKW4EOGDkqhbQyqxjsKzMgM/P1ymhlO5C4ezK4DeS/c7RdzQoa3x1PaWenJjJZwT9rQ1gSp/js1jYoZdyfX8M1/mp7uFaTR8mrt29FEMQILr62jQ1I5kA8OF59jIItVA78dJertTiBNs1ZKfLNG+MUHX1oaURtIHEAOw3p/Y197MWHEJEUGCxwfHj8MTZIcnsGKxzrIURYzPLnJgbxvG2hMrKdjItjbV11CYKeG8R7ygIdB3sBMFhkem0RAAQ3Fuka7UZtRHrasOqhYNilOwrkrwnhCU/ON5/q04vHV48ThxOCuoAbxnBQB+am65QnO8FqMxNCjBe14mpHhxBBGCWBLxD3iyWMaYMLUKsO7WYH6Stk1xCAGccmR/Ozs/bKJuXS39R/YgIjgROloSDA39Deit1SZWotsjD8pfp5ONqZ6uTfyWn+T7X0f59t5fqDhUA4ry0fYtjJcWeZQvTBu4/VqRuk9/l9Fy5cbnX+6Od26s58HjWWaflwkusKGxjm1bmhkvLXHvh1+WMbWncgPfZN+qcvex6xnUXkzvSiYP7EvTvH4toDxdqDD4+ygT+cKMMbH+3MCZ7H9uAaDnqytpVX8cDScJlRY0YIwpAjcNcuePgXP/P6Z30QuoP4J7WbYhuQAAAABJRU5ErkJggg==);
}

#yapep-debug-toolbar .yapep-debug-error {
	background-image:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAIsSURBVDjLpVNLSJQBEP7+h6uu62vLVAJDW1KQTMrINQ1vPQzq1GOpa9EppGOHLh0kCEKL7JBEhVCHihAsESyJiE4FWShGRmauu7KYiv6Pma+DGoFrBQ7MzGFmPr5vmDFIYj1mr1WYfrHPovA9VVOqbC7e/1rS9ZlrAVDYHig5WB0oPtBI0TNrUiC5yhP9jeF4X8NPcWfopoY48XT39PjjXeF0vWkZqOjd7LJYrmGasHPCCJbHwhS9/F8M4s8baid764Xi0Ilfp5voorpJfn2wwx/r3l77TwZUvR+qajXVn8PnvocYfXYH6k2ioOaCpaIdf11ivDcayyiMVudsOYqFb60gARJYHG9DbqQFmSVNjaO3K2NpAeK90ZCqtgcrjkP9aUCXp0moetDFEeRXnYCKXhm+uTW0CkBFu4JlxzZkFlbASz4CQGQVBFeEwZm8geyiMuRVntzsL3oXV+YMkvjRsydC1U+lhwZsWXgHb+oWVAEzIwvzyVlk5igsi7DymmHlHsFQR50rjl+981Jy1Fw6Gu0ObTtnU+cgs28AKgDiy+Awpj5OACBAhZ/qh2HOo6i+NeA73jUAML4/qWux8mt6NjW1w599CS9xb0mSEqQBEDAtwqALUmBaG5FV3oYPnTHMjAwetlWksyByaukxQg2wQ9FlccaK/OXA3/uAEUDp3rNIDQ1ctSk6kHh1/jRFoaL4M4snEMeD73gQx4M4PsT1IZ5AfYH68tZY7zv/ApRMY9mnuVMvAAAAAElFTkSuQmCC);
}

#yapep-debug-toolbar .yapep-debug-sql {
	background-image:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAEYSURBVBgZBcHPio5hGAfg6/2+R980k6wmJgsJ5U/ZOAqbSc2GnXOwUg7BESgLUeIQ1GSjLFnMwsKGGg1qxJRmPM97/1zXFAAAAEADdlfZzr26miup2svnelq7d2aYgt3rebl585wN6+K3I1/9fJe7O/uIePP2SypJkiRJ0vMhr55FLCA3zgIAOK9uQ4MS361ZOSX+OrTvkgINSjS/HIvhjxNNFGgQsbSmabohKDNoUGLohsls6BaiQIMSs2FYmnXdUsygQYmumy3Nhi6igwalDEOJEjPKP7CA2aFNK8Bkyy3fdNCg7r9/fW3jgpVJbDmy5+PB2IYp4MXFelQ7izPrhkPHB+P5/PjhD5gCgCenx+VR/dODEwD+A3T7nqbxwf1HAAAAAElFTkSuQmCC);
}

#yapep-debug-toolbar .yapep-debug-cache {
	background-image:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAEYSURBVBgZBcHPio5hGAfg6/2+R980k6wmJgsJ5U/ZOAqbSc2GnXOwUg7BESgLUeIQ1GSjLFnMwsKGGg1qxJRmPM97/1zXFAAAAEADdlfZzr26miup2svnelq7d2aYgt3rebl585wN6+K3I1/9fJe7O/uIePP2SypJkiRJ0vMhr55FLCA3zgIAOK9uQ4MS361ZOSX+OrTvkgINSjS/HIvhjxNNFGgQsbSmabohKDNoUGLohsls6BaiQIMSs2FYmnXdUsygQYmumy3Nhi6igwalDEOJEjPKP7CA2aFNK8Bkyy3fdNCg7r9/fW3jgpVJbDmy5+PB2IYp4MXFelQ7izPrhkPHB+P5/PjhD5gCgCenx+VR/dODEwD+A3T7nqbxwf1HAAAAAElFTkSuQmCC);
}

#yapep-debug-toolbar .yapep-debug-curl {
	background-image:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAEYSURBVBgZBcHPio5hGAfg6/2+R980k6wmJgsJ5U/ZOAqbSc2GnXOwUg7BESgLUeIQ1GSjLFnMwsKGGg1qxJRmPM97/1zXFAAAAEADdlfZzr26miup2svnelq7d2aYgt3rebl585wN6+K3I1/9fJe7O/uIePP2SypJkiRJ0vMhr55FLCA3zgIAOK9uQ4MS361ZOSX+OrTvkgINSjS/HIvhjxNNFGgQsbSmabohKDNoUGLohsls6BaiQIMSs2FYmnXdUsygQYmumy3Nhi6igwalDEOJEjPKP7CA2aFNK8Bkyy3fdNCg7r9/fW3jgpVJbDmy5+PB2IYp4MXFelQ7izPrhkPHB+P5/PjhD5gCgCenx+VR/dODEwD+A3T7nqbxwf1HAAAAAElFTkSuQmCC);
}

#yapep-debug-toolbar .yapep-debug-time {
	background-image:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAKrSURBVDjLpdPbT9IBAMXx/qR6qNbWUy89WS5rmVtutbZalwcNgyRLLMyuoomaZpRQCt5yNRELL0TkBSXUTBT5hZSXQPwBAvor/fZGazlb6+G8nIfP0znbgG3/kz+Knsbb+xxNV63DLxVLHzqV0vCrfMluzFmw1OW8ePEwf8+WgM1UXDnapVgLePr5Nj9DJBJGFEN8+TzKqL2RzkenV4yl5ws2BXob1WVeZxXhoB+PP0xzt0Bly0fKTePozV5GphYQPA46as+gU5/K+w2w6Ev2Ol/KpNCigM01R2uPgDcQIRSJEYys4JmNoO/y0tbnY9JlxnA9M15bfHZHCnjzVN4x7TLz6fMSJqsPgLAoMvV1niSQBGIbUP3Ki93t57XhItVXjulTQHf9hfk5/xgGyzQTgQjx7xvE4nG0j3UsiiLR1VVaLN3YpkTuNLgZGzRSq8wQUoD16flkOPSF28/cLCYkwqvrrAGXC1UYWtuRX1PR5RhgTJTI1Q4wKwzwWHk4kQI6a04nQ99mUOlczMYkFhPrBMQoN+7eQ35Nhc01SvA7OEMSFzTv8c/0UXc54xfQcj/bNzNmRmNy0zctMpeEQFSio/cdvqUICz9AiEPb+DLK2gE+2MrR5qXPpoAn6mxdr1GBwz1FiclDcAPCEkTXIboByz8guA75eg8WxxDtFZloZIdNKaDu5rnt9UVHE5POep6Zh7llmsQlLBNLSMTiEm5hGXXDJ6qb3zJiLaIiJy1Zpjy587ch1ahOKJ6XHGGiv5KeQSfFun4ulb/josZOYY0di/0tw9YCquX7KZVnFW46Ze2V4wU1ivRYe1UWI1Y1vgkDvo9PGLIoabp7kIrctJXSS8eKtjyTtuDErrK8jIYHuQf8VbK0RJUsLfEg94BfIztkLMvP3v3XN/5rfgIYvAvmgKE6GAAAAABJRU5ErkJggg==);
}

#yapep-debug-toolbar .yapep-debug-memory {
	background-image:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABmJLR0QA/gD+AP7rGNSCAAAACXBIWXMAAABIAAAASABGyWs+AAAACXZwQWcAAAAQAAAAEABcxq3DAAACQklEQVQ4y3WTMY/VRRTFf2fmv++xu3mQ8CJElMK4vtUFiZFOW43bQAJRWiv8Cn4GP4LGxpJCGi3chNZgQaDAAM2yjSHiM6xkX4Lum3uPxf8tQsxOcnInd3LvOefOjK5c3gQ4BnwD/AB8BPwIfAzsAr8B54GfgEvAt8AXwFXgaT3zzpsjlXqmSJ9KxaCJJEm8jTQCjYA3ABk2sOeS3pd0B3iqzy59cm14ZHlj7a2Ns6PRKDKjZL+UmTiTzNAiV+zM3d0ndTp9/Ksz73W2v661+3wyWT+7vLJSZ7MZzqiZSWSQkWQGmVEzgtXV1Tqb7TH94/fb2N91kq5GtHcjg52dHf6cTokMIoJojYgFWqO1OePxmFOvncL2eaRBAV+Xyv2eJTmQ7QzswAd550vnwH3gegFtOmPNaTLiP8YFa0SQz/eNAyLba8BmAW6BHvWeXyxe2Hix4QLuGzwCbnXAaTuP2rlQEIco6WNGYABxFDjdAcdtL2fm88Flxv9sHCBtJCG0DBwvwBbSdmYeOoOIRkbDTiSQCkjbwFYn6YIzJ3bS2pz5fJ9eTXvpGtMGG1SoSwNKKRNnXuhs3wCOSbx34uRJalfxwk5m7zltAFQKr5x4lSMrI0o3eNj+eXajk3TR9vrD7W3G4zHDwaB/vk4yu550IbsuDSndkMdP9iilrgMXdeXy5kjSh8BXmb4Lnti+B0xsPwP+kvQ68EClnCt16Zda6wfO/LLt//1zB+zZvgk8kNgCNUmHfeehY/59a/tj4KakvX8BD1rpHWtyUm4AAAAuelRYdGNyZWF0ZS1kYXRlAAB42jMyMLDQNbDQNTIJMTCwMjKzMjTXNTC1MjAAAEIJBRdq+VPsAAAALnpUWHRtb2RpZnktZGF0ZQAAeNozMjCw0DWw0DUyCDE0tTKxtDIy0zUwtTIwAABCTQUe2RFkjgAAAABJRU5ErkJggg==);
}

#yapep-debug-toolbar a:hover span {
	background-color:#eee;
}

.yapep-debug-panel {
	z-index:5;
	width:100%;
	position:absolute;
	right:0;
	top:0;
	display:none;
	margin:0;
	padding:0;
}

.yapep-debug-panel-inner {
	background:#eee;
	border:1px solid #bbb;
	border-top:none;
	-moz-border-radius-bottomright:20px;
	-moz-border-radius-bottomleft:20px;
	-webkit-border-bottom-right-radius:20px;
	-webkit-border-bottom-left-radius:20px;
	-webkit-box-shadow:0 5px 10px #888;
	-moz-box-shadow:0 5px 10px #888;
	margin:0 40px;
	padding:0 40px;
}

.yapep-debug-panel h2 {
	font-size:20px;
	margin: 10px 0 15px;
}

.yapep-debug-panel h3 {
	margin: 10px 0 10px;
	font-size:16px;
}

.yapep-debug-panel h3.yapep-debug-clickable {
	background:#ddd url(data:image/gif;base64,R0lGODlhBwAHAMQAAMLCwv///62trbS0tJqamn9/f7q6unR0dKysrJ2dnbOzs6+vr3Z2dqmpqYSEhJCQkLu7u729vXl5eYeHh4qKiqOjo9PT07+/vwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAAAAAAALAAAAAAHAAcAAAUioHUZw9JAEaCqxAAIgQA8CBDcwFS9MVAQKxVDQXEUJIdECAA7) no-repeat 6px 6px;
	margin-bottom:10px;
	padding:3px 0 3px 19px;
}

.yapep-debug-panel table {
	display:none;
	font-size:12px;
	border-spacing:0;
	border-collapse:collapse;
	border:1px solid #000;
	margin:25px 0;
}

.yapep-debug-panel .yapep-debug-panel-inner-summary table {
	display:block;
}

#yapep-debug-panel-memory table, #yapep-debug-panel-time table {
	display: block;
}

.yapep-debug-panel tbody tr:hover {
	background:#cff!important;
}

.yapep-debug-panel table tr.odd {
	background:#ddd;
}

.yapep-debug-panel table th,.yapep-debug-panel table td {
	text-align:left;
	vertical-align:top;
	padding:3px 10px;
}

.yapep-debug-panel table thead tr {
	background:#bbb;
	border-bottom:2px solid #000;
}

.yapep-debug-panel table thead th,.yapep-debug-panel table thead td {
	text-align:center;
}

.yapep-debug-panel table pre {
	white-space:normal;
	margin:0;
}

.yapep-debug-code {
	font-family:monospace;
	font-size:11px;
	list-style:decimal-leading-zero;
	border:1px solid #bbb;
	padding-left: 0;
}

.yapep-debug-code var {
	text-decoration: underline;
	font-weight: bold;
	font-style: normal !important;
	cursor: pointer;
}

.yapep-debug-code li {
	line-height:15px;
	padding-left: 10px;
	margin-left: 0;
}

.yapep-debug-code .odd {
	background:#ddd!important;
}

.yapep-debug-code .yapep-debug-code-highlight,
.yapep-debug-code .odd.yapep-debug-code-highlight {
	background:#fcc!important;
	list-style: none url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAICAYAAADwdn+XAAAABGdBTUEAANbY1E9YMgAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAADtSURBVHjaYvj//z8Dsdh/pvtuz4mObMhiTAwkgF8/ftn8/P7rKrIYI8iU0hOZ/0Gcv3/+Mvz5/Yfh968/DL9+/gZqAOFfDD+BGERLCkky/Pr1m+Hm7VuXTrVf0AfpYYGZZCVsy/D3H9CAf3/g+Pffv0D8m+H3PyD+CxP7w/Dly1c9rSy1k9em3TJngdkMUgjXDFT0G6z4N4L+i5D79fs3yJW/4S4AOXv/w71YnQ30M5jNxcHFICkpxfD61WuG2zfuXrg9554NPAyIAQaF2hZAC/YDbX4E1KwOEyc6Fi70Xz0B1HwQ6FpdZHGAAAMAuwDj/W9ARccAAAAASUVORK5CYII=);
}

.yapep-debug-code li pre {
	margin:0;
}

.yapep-debug-error-item {
	border-bottom:1px dotted #bbb;
}

.yapep-debug-error-item p {
	line-height: 140%;
	background: #ddd;
	padding: 6px 4px;
}

.yapep-debug-error-item p strong {
	color:#d33;
}

.yapep-debug-error-item var {
	font-style:italic;
}


.yapep-debug-error-item .yapep-debug-container {
	display:none;
	margin-left: 80px;
}

.yapep-debug-error-item .yapep-debug-container .yapep-debug-code {
	margin-left: 40px;
}

.yapep-debug-error-item .yapep-debug-code {
	margin:10px 80px 10px 120px;
}

.yapep-debug-collapse-all {
	border: none;
	padding: 10px;
	text-align: left;
}

</style>

<div id="yapep-debug" class="<?= (count($errors) > 0 ? 'yapep-debug-error' : '') ?>">
	<div id="yapep-debug-toolbar">
		<div style="float: left; padding: 5px 4px;" class="yapep-debug-clickable" onclick="Yapep.setCookie('yapepStatus', Yapep.toggle('toolbar-menu'), new Date(2040, 1, 1), '/'); return false;">
			&#x25BA;
		</div>

		<ul id="yapep-debug-toolbar-menu">
			<li>
				<span class="yapep-debug-clickable yapep-debug-log" onclick="Yapep.toggle('panel-log'); return false;">
					Log
				</span>
			</li>
			<li>
				<span class="yapep-debug-clickable yapep-debug-message" onclick="Yapep.toggle('panel-message'); return false;">
					Info (<?= count($messages) ?>)
				</span>
			</li>
			<li>
				<span class="yapep-debug-clickable yapep-debug-error" onclick="Yapep.toggle('panel-error'); return false;">
					Error (<?= count($errors) ?>)
				</span>
			</li>
			<li>
				<span class="yapep-debug-clickable yapep-debug-sql" onclick="Yapep.toggle('panel-sql'); return false;">
					SQL (<?= count($sqlQueries) ?> in <?= number_format($sqlQueryTimes * 1000, 2) ?>ms)
				</span>
			</li>
			<li>
				<span class="yapep-debug-clickable yapep-debug-cache" onclick="Yapep.toggle('panel-cache'); return false;">
					CACHE (<?= count($cacheRequests) ?> in <?= number_format($cacheTimes * 1000, 2) ?>ms)
				</span>
			</li>
			<li>
				<span class="yapep-debug-clickable yapep-debug-curl" onclick="Yapep.toggle('panel-curl'); return false;">
					CURL (<?= count($curlRequests) ?> in <?= number_format($curlTimes * 1000, 2) ?>ms)
				</span>
			</li>
			<li>
				<span class="yapep-debug-clickable yapep-debug-time" onclick="Yapep.toggle('panel-time')">
					<?= number_format($this->runTime * 1000, 2) ?> ms
				</span>
			</li>
			<li>
				<span class="yapep-debug-clickable yapep-debug-memory" onclick="Yapep.toggle('panel-memory')">
					<?= round(memory_get_peak_usage(true) / 1024 / 1024, 2) ?> MB
				</span>
			</li>
		</ul>
	</div>

	<div id="yapep-debug-panel-log" class="yapep-debug-panel">
		<div class="yapep-debug-panel-inner">
			<br style="clear: both;"/>
			<h2>Log</h2>

		<?php foreach (array_filter(array(
				'server'  => $this->serverParams,
				'cookies' => $this->cookieParams,
				'get'     => $this->getParams,
				'post'    => $this->postParams,
				'session' => $this->sessionParams
			)) as $name => $var):
		?>
			<h3 class="yapep-debug-clickable" onclick="Yapep.toggle('panel-log-<?= $name ?>', []); return false;">
				<?= strtoupper($name) ?>
			</h3>
			<table id="yapep-debug-panel-log-<?= $name ?>">
				<thead>
				<tr><th>Variable name</th><th>Value</th></tr>
				</thead>
				<tbody>
				<?php $i = 0; foreach ($var as $key => $value): ?>
					<tr class="<?= (++$i % 2 ? 'odd' : '') ?>">
					<th><var><?= $key ?></var></th>
					<td>
					<?php if (is_array($value)): ?>
						<table style="display: block; margin: 0; border: 0;">
						<?php foreach ($value as $valueKey => $valueValue): ?>
							<tr>
								<th><?= $valueKey ?></th>
								<td><pre><?= print_r($valueValue, true) ?></pre></td>
							</tr>
						<?php endforeach; ?>
						</table>
					<?php else: ?>
						<pre><?= print_r($value, true) ?></pre>
					<?php endif;?>
					</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endforeach; ?>
			<br style="clear: both;"/>
		</div>
	</div>


	<div id="yapep-debug-panel-message" class="yapep-debug-panel">
		<div class="yapep-debug-panel-inner">
			<br style="clear: both;"/>
			<h2>Messages</h2>
		<?php
			foreach ($messages as $message):
				/** @var \YapepBase\Debugger\Item\MessageItem $message */
				$messageData = $this->viewDo->escape($message->getData());
		?>
			<?php if (is_string($messageData[MessageItem::LOCAL_FIELD_MESSAGE])): ?>
				<p>
					<code>"<?= $messageData[MessageItem::LOCAL_FIELD_MESSAGE] ?>"</code>
					&#8212; <em style="background: #ccc; font-style: italic;"><?= $messageData[MessageItem::FIELD_FILE] ?> (line <?= $messageData[MessageItem::FIELD_LINE] ?>)</em>
				</p>
				<?php else: ?>
				<p>
					<em style="background: #ccc; font-style: italic;"><?= $messageData[MessageItem::FIELD_FILE] ?> (line <?= $messageData[MessageItem::FIELD_LINE] ?>)</em>
				<pre><?= print_r($messageData[MessageItem::LOCAL_FIELD_MESSAGE], true) ?></pre>
				</p>
				<?php endif; ?>
		<?php endforeach; ?>
			<br style="clear: both;"/>
		</div>
	</div>


	<div id="yapep-debug-panel-error" class="yapep-debug-panel">
		<div class="yapep-debug-panel-inner">
			<br style="clear: both;"/>
			<h2>Error</h2>
		<?php
			if (!empty($errors)):
				$locationIdCount = array();
				foreach ($errors as $error) {
					/** @var \YapepBase\Debugger\Item\ErrorItem $error */
					$locationId = $error->getLocationId();
					if (isset($locationIdCount[$locationId])) {
						$locationIdCount[$locationId]++;
					} else {
						$locationIdCount[$locationId] = 1;
					}
				}
				array_multisort($locationIdCount);
				$locationIdCount = array_reverse($locationIdCount, true);
		?>
			<div class="yapep-debug-error-item">
				<h3 class="yapep-debug-clickable" onclick="Yapep.toggle('ERRORSUMMARY'); return false;">Summary</h3>
				<div class="yapep-debug-panel-inner-summary" id="yapep-debug-ERRORSUMMARY">
					<table>
						<tr><th>Source</th><th>Count</th></tr>
					<?php foreach($locationIdCount as $source => $count): ?>
						<tr><td><?= $this->viewDo->escape($source) ?></td><td><?=$count?></td></tr>
					<?php endforeach; ?>
					</table>
				</div>
			</div>
		<?php endif;?>

			<p class="yapep-debug-clickable yapep-debug-collapse-all" onclick="Yapep.collapseAll(event); return false;">Collapse all</p>
			<?php foreach ($errors as $error): ?>
				<?= $this->getFormattedError($error) ?>
			<?php endforeach; ?>
			<br style="clear: both;"/>
		</div>
	</div>


	<div id="yapep-debug-panel-sql" class="yapep-debug-panel">
		<div class="yapep-debug-panel-inner">
			<br style="clear: both;"/>
			<h2>SQL</h2>

			<?php
				if (!empty($sqlQueries)):
					$locationIdCount = array();
					$connectionStat = array();
					foreach ($sqlQueries as $query) {
						/** @var \YapepBase\Debugger\Item\SqlQueryItem $query */
						$locationId = $query->getLocationId();
						if (isset($locationIdCount[$locationId])) {
							$locationIdCount[$locationId]++;
						} else {
							$locationIdCount[$locationId] = 1;
						}
						$connectionName = $query->getField(SqlQueryItem::LOCAL_FIELD_CONNECTION_NAME);
						if (isset($connectionStat[$connectionName])) {
							$connectionStat[$connectionName]['time'] +=
								$query->getField(SqlQueryItem::LOCAL_FIELD_EXECUTION_TIME);
							$connectionStat[$connectionName]['count']++;
						} else {
							$connectionStat[$connectionName] = array(
								'time'  => $query->getField(SqlQueryItem::LOCAL_FIELD_EXECUTION_TIME),
								'count' => 1,
							);
						}
					}
					array_multisort($locationIdCount);
					$locationIdCount = array_reverse($locationIdCount, true);
			?>
			<div class="yapep-debug-error-item">
				<h3 class="yapep-debug-clickable" onclick="Yapep.toggle('DBSUMMARY'); return false;">Summary</h3>
				<div class="yapep-debug-panel-inner-summary" id="yapep-debug-DBSUMMARY">
					<table>
						<tr><th>Source</th><th>Count</th></tr>
					<?php foreach($locationIdCount as $source => $count): ?>
						<tr><td><?= $this->viewDo->escape($source) ?></td><td><?=$count?></td></tr>
					<?php endforeach; ?>
					</table>
				</div>
			</div>
			<div class="yapep-debug-error-item">
				<h3 class="yapep-debug-clickable" onclick="Yapep.toggle('DBCONNECTIONSUMMARY'); return false;">Connection summary</h3>
				<div class="yapep-debug-panel-inner-summary" id="yapep-debug-DBCONNECTIONSUMMARY">
					<table>
						<tr>
							<th>Connection</th>
							<th>Query count</th>
							<th>Total time</th>
							<th>Visible</th>
						</tr>
						<?php foreach($connectionStat as $name => $data): ?>
						<tr>
							<td><label for="yapep-debug-db-connection-toggle-<?= $this->viewDo->escape($name) ?>"><?= $this->viewDo->escape($name) ?></label></td>
							<td><?= $data['count'] ?></td>
							<td><?= sprintf('%.4f', $data['time']) ?></td>
							<td><input type="checkbox" id="yapep-debug-db-connection-toggle-<?= $name ?>" checked="checked" onchange="Yapep.toggleClassByCheckboxStatus(this)" rel="yapep-debug-db-connection-<?= $this->viewDo->escape($name) ?>"/> </td>
						</tr>
						<?php endforeach; ?>
					</table>
				</div>
			</div>
			<?php endif;?>

			<p class="yapep-debug-clickable yapep-debug-collapse-all" onclick="Yapep.collapseAll(event); return false;">Collapse all</p>
		<?php
			foreach ($sqlQueries as $index => $query):
				/** @var \YapepBase\Debugger\Item\SqlQueryItem $query */
				$queryData = $this->viewDo->escape($query->getData());
				$queryCleared = $this->viewDo->escape($this->formatSqlQuery($queryData[SqlQueryItem::LOCAL_FIELD_QUERY],
					$queryData[SqlQueryItem::LOCAL_FIELD_PARAMS]));
		?>
			<div class="yapep-debug-error-item yapep-debug-collapse-all yapep-debug-db-connection-<?= $queryData[SqlQueryItem::LOCAL_FIELD_CONNECTION_NAME] ?>">
				<p class="yapep-debug-clickable" onclick="Yapep.toggle('SQL<?= $index ?>'); return false;">
					<?= (isset($queryData[SqlQueryItem::LOCAL_FIELD_EXECUTION_TIME]) ? sprintf('%.4f', $queryData[SqlQueryItem::LOCAL_FIELD_EXECUTION_TIME]) : '') ?> sec in
					<var><?= $queryData[SqlQueryItem::FIELD_FILE] ?></var>,
					<u>line <?= $queryData[SqlQueryItem::FIELD_LINE] ?></u>
					connection: <strong><?= $queryData[SqlQueryItem::LOCAL_FIELD_CONNECTION_NAME] ?></strong>
				</p>
					<ol class="yapep-debug-code yapep-debug-copyable" title="Double-click to copy content" id="yapep-debug-SQL<?= $index ?>" ondblclick="Yapep.copyToClipboard(this); return false;">
					<?php foreach (explode("\n", $queryCleared) as $lineIndex => $line): ?>
						<li class="<?= ($lineIndex % 2 ? 'odd' : '') ?>">
							<pre><?= $line ?></pre>
						</li>
					<?php endforeach; ?>
				</ol>
			</div>
		<?php endforeach; ?>
			<br style="clear: both;"/>
		</div>
	</div>


	<div id="yapep-debug-panel-cache" class="yapep-debug-panel">
		<div class="yapep-debug-panel-inner">
			<br style="clear: both;"/>
			<h2>CACHE</h2>

		<?php
			if (!empty($cacheRequests)):
				$locationIdCount = array();
				$connectionStat = array();
				foreach ($cacheRequests as $request) {
					/** @var \YapepBase\Debugger\Item\StorageItem $request */
					$locationId = $request->getLocationId();
					if (isset($locationIdCount[$locationId])) {
						$locationIdCount[$locationId]++;
					} else {
						$locationIdCount[$locationId] = 1;
					}
					$connectionName = $request->getField(SqlQueryItem::LOCAL_FIELD_CONNECTION_NAME);
					if (isset($connectionStat[$connectionName])) {
						$connectionStat[$connectionName]['time'] +=
							$request->getField(SqlQueryItem::LOCAL_FIELD_EXECUTION_TIME);
						$connectionStat[$connectionName]['count']++;
					} else {
						$connectionStat[$connectionName] = array(
							'time'  => $request->getField(SqlQueryItem::LOCAL_FIELD_EXECUTION_TIME),
							'count' => 1,
						);
					}
				}
				array_multisort($locationIdCount);
				$locationIdCount = array_reverse($locationIdCount, true);
		?>
			<div class="yapep-debug-error-item">
				<h3 class="yapep-debug-clickable" onclick="Yapep.toggle('CACHESUMMARY'); return false;">Summary</h3>
				<div class="yapep-debug-panel-inner-summary" id="yapep-debug-CACHESUMMARY">
					<table>
						<tr><th>Source</th><th>Count</th></tr>
						<?php foreach($locationIdCount as $source => $count): ?>
						<tr><td><?= $this->viewDo->escape($source) ?></td><td><?=$count?></td></tr>
						<?php endforeach; ?>
					</table>
				</div>
			</div>
			<div class="yapep-debug-error-item">
				<h3 class="yapep-debug-clickable" onclick="Yapep.toggle('CACHECONNECTIONSUMMARY'); return false;">Connection summary</h3>
				<div class="yapep-debug-panel-inner-summary" id="yapep-debug-CACHECONNECTIONSUMMARY">
					<table>
						<tr>
							<th>Connection</th>
							<th>Request count</th>
							<th>Total time</th>
							<th>Visible</th>
						</tr>
						<?php foreach($connectionStat as $name => $data): ?>
						<tr>
							<td><label for="yapep-debug-cache-connection-toggle-<?= $this->viewDo->escape($name) ?>"><?= $this->viewDo->escape($name) ?></label></td>
							<td><?= $data['count'] ?></td>
							<td><?= sprintf('%.4f', $data['time']) ?></td>
							<td><input type="checkbox" id="yapep-debug-cache-connection-toggle-<?= $name ?>" checked="checked" onchange="Yapep.toggleClassByCheckboxStatus(this)" rel="yapep-debug-cache-connection-<?= $this->viewDo->escape($name) ?>"/> </td>
						</tr>
						<?php endforeach; ?>
					</table>
				</div>
			</div>
			<?php endif;?>

			<p class="yapep-debug-clickable yapep-debug-collapse-all" onclick="Yapep.collapseAll(event); return false;">Collapse all</p>
		<?php
			foreach ($cacheRequests as $index => $request):
				/** @var \YapepBase\Debugger\Item\StorageItem $request */
				$requestData = $this->viewDo->escape($request->getData());
		?>
			<div class="yapep-debug-error-item yapep-debug-cache-connection-<?= $requestData[StorageItem::LOCAL_FIELD_CONNECTION_NAME] ?>">
				<p class="yapep-debug-clickable" onclick="Yapep.toggle('CACHE<?= $index ?>'); return false;">
					<b><?= $requestData[StorageItem::LOCAL_FIELD_QUERY] ?></b> ->
					<b><?= (isset($requestData[StorageItem::LOCAL_FIELD_EXECUTION_TIME]) ? sprintf('%.4f', $requestData[StorageItem::LOCAL_FIELD_EXECUTION_TIME]) : '') ?></b> sec in  ||
					<var><?= $requestData[StorageItem::FIELD_FILE] ?></var>,
					<u>line <?= $requestData[StorageItem::FIELD_LINE] ?></u>
					connection: <strong><?= $requestData[StorageItem::LOCAL_FIELD_CONNECTION_NAME] ?></strong>
				</p>
				<ol class="yapep-debug-code yapep-debug-copyable" title="Double-click to copy content" id="yapep-debug-CACHE<?= $index ?>" ondblclick="Yapep.copyToClipboard(this); return false;">
				<?php
					$value = var_export($requestData[StorageItem::LOCAL_FIELD_PARAMS], true);
					foreach (explode("\n", $value) as $paramIndex => $line):
				?>
					<li class="<?= ($paramIndex % 2 ? 'odd' : '') ?>">
						<?= $line ?>
					</li>
				<?php endforeach; ?>
				</ol>
			</div>
		<?php endforeach; ?>
			<br style="clear: both;"/>
		</div>
	</div>


	<div id="yapep-debug-panel-curl" class="yapep-debug-panel">
		<div class="yapep-debug-panel-inner">
			<br style="clear: both;"/>
			<h2>CURL</h2>
		<?php if (!empty($curlRequests)): ?>
			<div class="yapep-debug-error-item">
				<h3 class="yapep-debug-clickable" onclick="Yapep.toggle('CURLSUMMARY'); return false;">Summary</h3>
				<?php
					$locationIdCount = array();
					$hostStat = array();
					foreach ($curlRequests as $request) {
						/** @var \YapepBase\Debugger\Item\CurlRequestItem $request */
						$locationId = $request->getLocationId();
						if (isset($locationIdCount[$locationId])) {
							$locationIdCount[$locationId]++;
						} else {
							$locationIdCount[$locationId] = 1;
						}
						$hostName = (string)$request->getField(CurlRequestItem::LOCAL_FIELD_HOST);
						if (isset($hostStat[$hostName])) {
							$hostStat[$hostName]['time'] +=
								$request->getField(SqlQueryItem::LOCAL_FIELD_EXECUTION_TIME);
							$hostStat[$hostName]['count']++;
						} else {
							$hostStat[$hostName] = array(
								'time'  => $request->getField(SqlQueryItem::LOCAL_FIELD_EXECUTION_TIME),
								'count' => 1,
							);
						}
					}
					array_multisort($locationIdCount);
					$locationIdCount = array_reverse($locationIdCount, true);
				?>
				<div class="yapep-debug-panel-inner-summary" id="yapep-debug-CURLSUMMARY">
					<table>
						<tr><th>Source</th><th>Count</th></tr>
					<?php foreach($locationIdCount as $source => $count): ?>
						<tr>
							<td><?= $this->viewDo->escape($source) ?></td>
							<td><?= $count ?></td>
						</tr>
					<?php endforeach; ?>
					</table>
				</div>
			</div>
			<div class="yapep-debug-error-item">
				<h3 class="yapep-debug-clickable" onclick="Yapep.toggle('CURLCONNECTIONSUMMARY'); return false;">Connection summary</h3>
				<div class="yapep-debug-panel-inner-summary" id="yapep-debug-CURLCONNECTIONSUMMARY">
					<table>
						<tr>
							<th>Connection</th>
							<th>Request count</th>
							<th>Total time</th>
							<th>Visible</th>
						</tr>
						<?php foreach($hostStat as $name => $data): ?>
						<tr>
							<td><label for="yapep-debug-curl-connection-toggle-<?= $this->viewDo->escape($name) ?>"><?= $this->viewDo->escape($name) ?></label></td>
							<td><?= $data['count'] ?></td>
							<td><?= sprintf('%.4f', $data['time']) ?></td>
							<td><input type="checkbox" id="yapep-debug-curl-connection-toggle-<?= $name ?>" checked="checked" onchange="Yapep.toggleClassByCheckboxStatus(this)" rel="yapep-debug-curl-connection-<?= $this->viewDo->escape($name) ?>"/> </td>
						</tr>
						<?php endforeach; ?>
					</table>
				</div>
			</div>
			<?php endif;?>

			<p class="yapep-debug-clickable yapep-debug-collapse-all" onclick="Yapep.collapseAll(event); return false;">Collapse all</p>
		<?php
			foreach ($curlRequests as $index => $request):
				/** @var \YapepBase\Debugger\Item\CurlRequestItem $requestData */
				$requestData = $this->viewDo->escape($request->getData());
		?>
			<div class="yapep-debug-error-item yapep-debug-curl-connection-<?= (string)$requestData[CurlRequestItem::LOCAL_FIELD_HOST] ?>"">
				<p class="yapep-debug-clickable" onclick="Yapep.toggle('CURL<?= $index ?>'); return false;">
					<b><?= $requestData[CurlRequestItem::LOCAL_FIELD_PROTOCOL] ?> <?= $requestData[CurlRequestItem::LOCAL_FIELD_METHOD] ?>: <?= $requestData[CurlRequestItem::LOCAL_FIELD_URL] ?></b> -&gt;
					<b><?= (isset($requestData[CurlRequestItem::LOCAL_FIELD_EXECUTION_TIME]) ? sprintf('%.4f', $query[CurlRequestItem::LOCAL_FIELD_EXECUTION_TIME]) : '') ?></b> sec in  ||
					<var><?= $requestData[CurlRequestItem::FIELD_FILE] ?></var>,
					<u>line <?= $requestData[CurlRequestItem::FIELD_LINE] ?></u>
				</p>
				<ol class="yapep-debug-code yapep-debug-copyable" title="Double-click to copy content" id="yapep-debug-CURL<?= $index ?>" ondblclick="Yapep.copyToClipboard(this); return false;">
				<?php
					$value = var_export($requestData[CurlRequestItem::LOCAL_FIELD_PARAMETERS], true);
					foreach (explode("\n", $value) as $valueIndex => $line):
				?>
					<li class="<?= ($valueIndex % 2 ? 'odd' : '') ?>">
						<pre><?= $line ?></pre>
					</li>
				<?php endforeach; ?>
				</ol>
			</div>
		<?php endforeach; ?>
			<br style="clear: both;"/>
		</div>
	</div>


	<div id="yapep-debug-panel-time" class="yapep-debug-panel">
		<div class="yapep-debug-panel-inner">
			<br style="clear: both;"/>
			<h2>Time</h2>
			<table>
				<thead>
				<tr><th>Name</th><th>Value</th></tr>
				</thead>
				<tbody>
				<?php
					foreach ($timeMilestones as $milestone):
						/** @var \YapepBase\Debugger\Item\TimeItem $milestone */
						$milestoneData = $this->viewDo->escape($milestone->getData());
				?>
					<tr>
						<th><?= $milestoneData[TimeItem::FIELD_NAME] ?></th>
						<td><?= sprintf('%.2f', ($milestoneData[TimeItem::LOCAL_FIELD_ELAPSED_TIME]) * 1000) ?> ms</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<br style="clear: both;"/>
		</div>
	</div>


	<div id="yapep-debug-panel-memory" class="yapep-debug-panel">
		<div class="yapep-debug-panel-inner">
			<br style="clear: both;"/>
			<h2>Memory Usage</h2>
			<table>
				<thead>
				<tr><th>Name</th><th>Real</th><th>Peak</th></tr>
				</thead>
				<tbody>
				<?php
					foreach ($memoryMilestones as $milestone):
						/** @var \YapepBase\Debugger\Item\MemoryUsageItem $milestone */
						$milestoneData = $this->viewDo->escape($milestone->getData());
				?>
					<tr>
						<th><?= $milestoneData[MemoryUsageItem::FIELD_NAME] ?></th>
						<td><?= sprintf('%.2f', ($milestoneData[MemoryUsageItem::LOCAL_FIELD_CURRENT]) / 1024) ?> KB</td>
						<td><?= sprintf('%.2f', ($milestoneData[MemoryUsageItem::LOCAL_FIELD_PEAK]) / 1024) ?> KB</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<br style="clear: both;"/>
		</div>
	</div>

	<iframe id="copy" width="1" height="1" style="position: absolute; left: -100px; top: -100px;"></iframe>

</div>

<script type="text/javascript">
var Yapep = {
	tableDisplay: (function()
	{
		var d = document.createElement('table');
		try {
			d.style.display = 'table';
			d = d.style.display;
			if (/Trident\/4\./.test(navigator.userAgent)) {
				d = 'block';
			}
		}
			// IE7- fails on setting display.
		catch(e) {
			d = 'block';
		}
		return d;
	})(),
	toggle: function(id)
	{
		var i = 0,
			elem = this.$(id),
			parentEl = elem.parentNode;

		if (elem) {
			if (elem.id == 'yapep-debug-toolbar-menu') {
				parentEl = document.getElementById('yapep-debug');
				for (; i < parentEl.children.length; i++) {
					if (parentEl.children[i].id
						&& parentEl.children[i].id.indexOf('yapep-debug-') === 0
						&& parentEl.children[i].id != 'yapep-debug-toolbar'
						) {
						this.hide(parentEl.children[i]);
					}
				}
			}
			else {
				for (; i < parentEl.children.length; i++) {
					if (parentEl.children[i].id
						&& parentEl.children[i].id.indexOf('yapep-debug-') === 0
						&& parentEl.children[i].id != 'yapep-debug-' + id
						&& parentEl.children[i].id != 'yapep-debug-toolbar'
						) {
						this.hide(parentEl.children[i]);
					}
				}
			}

			if (this.currStyle(elem, 'display') === 'none') {
				this.show(elem);
				return 'visible';
			}
			else {
				this.hide(elem);
				return 'hidden';
			}
		}
		return null;
	},
	toggleClassByCheckboxStatus: function(checkboxElement) {
		var nodes = this.getElementsByClassName(checkboxElement.getAttribute('rel',	false));
		var displayValue = (checkboxElement.checked ? 'block' : 'none');
		for (var i in nodes) {
			nodes[i].style.display = displayValue;
		}

	},
	getElementsByClassName: function(className) {
		var retnode = [];
		var myclass = new RegExp('\\b'+className+'\\b');
		var elem = document.getElementsByTagName('*');
		for (var i = 0; i < elem.length; i++) {
			var classes = elem[i].className;
			if (myclass.test(classes))
				retnode.push(elem[i]);
		}
		return retnode;
	},
	collapseAll: function(event) {
		var sourceEl = event.target;
		var panelEl = sourceEl.parentNode;

		for(var i=0; i<panelEl.children.length; i++) {
			var el = panelEl.children[i];
			for (var j=1; j<el.children.length; j++) {
				var hideEl = el.children[j];
				var yapepId = hideEl.id.replace('yapep-debug-', '');
				if (hideEl.className.indexOf('yapep-debug-code') != -1 && yapepId.length > 0) {
					this.hide(hideEl);
				}
			}
		}
	},
	hide: function(elem)
	{
		elem.style.display = 'none';
	},
	show: function(elem)
	{
		elem.style.display = (elem.tagName === 'TABLE' ? this.tableDisplay : 'block');
	},
	$: function(id)
	{
		return document.getElementById('yapep-debug-'+id);
	},
	currStyle: function(elem, name)
	{
		var ret, style = elem.style;
		if (style && style[name]) {
			ret = style[name];
		}
		else if (typeof getComputedStyle !== 'undefined') {
			if (/float/i.test(name) ) {
				name = 'float';
			}
			name = name.replace(/([A-Z])/g, '-$1').toLowerCase();
			var defaultView = elem.ownerDocument.defaultView;
			if (!defaultView) {
				return null;
			}
			var computedStyle = defaultView.getComputedStyle(elem, null);
			if (computedStyle) {
				ret = computedStyle.getPropertyValue(name);
			}
			if (name === 'opacity' && ret === '') {
				ret = '1';
			}
		}
		else if (elem.currentStyle) {
			var camelCase = name.replace(/-([a-z])/ig, function(all, letter)
			{
				return letter.toUpperCase();
			});
			ret = elem.currentStyle[name] || elem.currentStyle[camelCase];
			if (!/^-?\d+(?:px)?$/i.test(ret) && /^-?\d/.test(ret)) {
				var left = style.left, rsLeft = elem.runtimeStyle.left;
				elem.runtimeStyle.left = elem.currentStyle.left;
				style.left = camelCase === 'fontSize' ? '1em' : (ret || 0);
				ret = style.pixelLeft + 'px';
				style.left = left;
				elem.runtimeStyle.left = rsLeft;
			}
		}
		return ret;
	},

	copyToClipboard: function(elem)
	{
		var text = elem.innerHTML
			.replace(/<\/?[^>]+>/g, '')
			.replace(/(^[\n\t]+|[\s\t\n]+$)/mg, '')
			.replace(/&lt;/g, '<')
			.replace(/&gt;/g, '>')
			.replace(/&quot;/g, '"')
			.replace(/&apos;/g, '\'')
			.replace(/&#39;/g, '\'')
			.replace(/&amp;/g, '&');

		if (navigator.userAgent.match(/windows/i)) {
			text = text.replace(/\n/g, '\r\n');
		}

		if (window.clipboardData && clipboardData.setData) {
			clipboardData.setData('Text', text);
		}
		else {
			try {
				netscape.security.PrivilegeManager.enablePrivilege('UniversalXPConnect');

				var clipboard = Components.classes['@mozilla.org/widget/clipboard;1'].createInstance(Components.interfaces.nsIClipboard),
					transferer = Components.classes['@mozilla.org/widget/transferable;1'].createInstance(Components.interfaces.nsITransferable),
					supportString = Components.classes['@mozilla.org/supports-string;1'].createInstance(Components.interfaces.nsISupportsString),
					iClipboard = Components.interfaces.nsIClipboard;

				transferer.addDataFlavor('text/unicode');
				supportString.data = text;
				transferer.setTransferData('text/unicode', supportString, text.length * 2);
				clipboard.setData(transferer, null, iClipboard.kGlobalClipboard);
			}
			catch (e) {
				alert('Your current Internet Security settings do not allow data copying to clipboard.\n\
				Please add \'signed.applets.codebase_principal_support\' to your about:config!');
				return false;
			}
		}
		return true;
	},
	setCookie: function(name, value, expires, path, domain, secure)
	{
		var text = encodeURIComponent(name) + '=' + encodeURIComponent(value);

		if (expires instanceof Date) {
			text += '; expires=' + expires.toUTCString();
		}

		if (path && path !== '') {
			text += '; path=' + path;
		}

		if (domain && domain !== '') {
			text += '; domain=' + domain;
		}

		if (secure === true) {
			text += '; secure';
		}

		document.cookie = text;
	},
	getCookie: function(name)
	{
		var cookies = {},
			cookieParts = document.cookie.split(/;\s+/g),
			i = 0,
			cookieCount = cookieParts.length,
			cookieNameValue,
			cookieValue,
			cookieName;

		for (; i < cookieCount; i++) {
			cookieNameValue = cookieParts[i].match(/^(.+?)=(.*)$/);
			if (cookieNameValue instanceof Array) {
				try {
					cookieName = decodeURIComponent(cookieNameValue[1]);
					cookieValue = decodeURIComponent(cookieNameValue[2]);
				}
				catch (e) {
				}
			}
			else {
				cookieName = decodeURIComponent(cookieParts[i]);
				cookieValue = '';
			}
			cookies[cookieName] = cookieValue;
		}

		return cookies[name] || null;
	},
	init: function()
	{
		if (this.getCookie('yapepStatus') == 'hidden') {
			this.hide(this.$('toolbar-menu'));
		}
		else {
			this.show(this.$('toolbar-menu'));
		}
	}
};
Yapep.init();
</script>

<?php // ------------------------ /HTML ---------------------
	}

}