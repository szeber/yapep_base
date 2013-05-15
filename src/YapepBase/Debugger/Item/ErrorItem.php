<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBase
 * @subpackage Debugger\Item
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Debugger\Item;

use YapepBase\Application;
use YapepBase\Config;

/**
 * Error debug item.
 *
 * Configuration options:
 * <ul>
 *   <li><b>system.debugger.errorDebugUrlTemplate</b>: The template for the full debug URL. It should contain a param,
 *       that will be replaced with the error ID. For example http://example.com/[ERROR_ID].log. Optional,
 *       if not set, no debug data URL will be returned.</li>
 *   <li><b>system.debugger.errorDebugUrlParamName</b>: The parameter that will be replaced in the debug URL.
 *       Example: [ERROR_ID]. Optional, if not set, no debug data URL will be returned.</li>
 * </ul>
 *
 * @package    YapepBase
 * @subpackage Debugger\Item
 */
class ErrorItem extends ItemAbstract implements ISourceLocatable {

	/** Error code field. */
	const LOCAL_FIELD_CODE = 'code';
	/** Error message field. */
	const LOCAL_FIELD_MESSAGE = 'message';
	/** Local context field. */
	const LOCAL_FIELD_CONTEXT = 'context';
	/** Error ID field. */
	const LOCAL_FIELD_ID = 'id';
	/** Field for the source code around the error source. */
	const LOCAL_FIELD_SOURCE_CODE = 'sourceCode';
	/** Field for the URL of the debug data file (trace file). */
	const LOCAL_FIELD_DEBUG_DATA_URL = 'debugDataUrl';

	/**
	 * Constructor.
	 *
	 * @param int    $code      The code of the error.
	 * @param string $message   Error message.
	 * @param string $file      The name of the file, where the error occured. If its empty,
	 *                             then it logs the file where logError() called from.
	 * @param int    $line      The number of the line where the error occured.If its <var>0</var>,
	 *                             then it logs the line where logError() called from.
	 * @param array  $context   The variables from the actual context.
	 * @param string $id        The id of the error.
	 */
	public function __construct($code, $message, $file, $line, array $context, $id) {
		if (empty($trace)) {
			$trace = array();
			if (empty($file) || $line === 0) {
				$trace = debug_backtrace(false);
				$file = $trace['file'];
				$line = $trace['line'];
			}
		}

		$this->data = array(
			self::LOCAL_FIELD_CODE           => $code,
			self::LOCAL_FIELD_MESSAGE        => $message,
			self::FIELD_FILE                 => $file,
			self::FIELD_LINE                 => $line,
			self::LOCAL_FIELD_CONTEXT        => $context,
			self::LOCAL_FIELD_ID             => $id,
			self::LOCAL_FIELD_SOURCE_CODE    => $this->getSource($file, $line),
			self::LOCAL_FIELD_DEBUG_DATA_URL => $this->getDebugDataUrl($id),
		);
	}

	/**
	 * Returns the field definitions as an associative array where the field name is the key,
	 * and the description is the value.
	 *
	 * @return array
	 */
	public function getFieldDefinitions() {
		return array(
			self::LOCAL_FIELD_CODE           => 'Error code',
			self::LOCAL_FIELD_MESSAGE        => 'Error message',
			self::FIELD_FILE                 => 'File',
			self::FIELD_LINE                 => 'Line',
			self::LOCAL_FIELD_CONTEXT        => 'Local context',
			self::LOCAL_FIELD_ID             => 'Error ID',
			self::LOCAL_FIELD_SOURCE_CODE    => 'Source code',
			self::LOCAL_FIELD_DEBUG_DATA_URL => 'URL to the debug data',
		);
	}

	/**
	 * Returns the item's type.
	 *
	 * The type should be unique for the debug item.
	 *
	 * @return string
	 */
	public function getType() {
		return self::DEBUG_ITEM_ERROR;
	}

	/**
	 * Returns the location ID for the item's source in file @ line format.
	 *
	 * @return string
	 */
	public function getLocationId() {
		return $this->data[self::FIELD_FILE] . ' @ ' . $this->data[self::FIELD_LINE];
	}

	/**
	 * Reads the given number of rows surrounding the given line of the file.
	 *
	 * @param string $file    Path of the file.
	 * @param int    $line    The number of the line.
	 * @param int    $range   Number of rows should be read before and after the given line.
	 *
	 * @return array   The rows indexed by the number of the rows.
	 *
	 * @todo Maybe it would make sense to move this method out to a helper class. [szeber]
	 */
	protected function getSource($file, $line, $range = 5) {
		$result = array();
		$buffer = file($file, FILE_IGNORE_NEW_LINES);
		if ($buffer !== false) {
			// We shift the ordinal numbers by one, to fit to the line numbers.
			array_unshift($buffer, null);
			unset($buffer[0]);
			$result = array_slice($buffer, max(0, $line - $range - 1), 2 * $range + 1, true);
		}
		return $result;
	}

	/**
	 * Returns the debug data URL for the specified error ID or an empty string, if the debug data url is not
	 * configured properly.
	 *
	 * @param string $errorId   The error ID.
	 *
	 * @return string
	 */
	protected function getDebugDataUrl($errorId) {
		$config = Config::getInstance();
		$urlTemplate = $config->get('system.debugger.errorDebugUrlTemplate', '');
		$paramName   = $config->get('system.debugger.errorDebugUrlParamName', '');

		if (empty($urlTemplate) || empty($paramName)) {
			return '';
		}

		return str_replace($paramName, $errorId, $urlTemplate);
	}
}
