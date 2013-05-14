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

/**
 * Log message debug item.
 *
 * @package    YapepBase
 * @subpackage Debugger\Item
 */
class CurlRequestItem extends ItemAbstract implements ISourceLocatable {

	/** Field for the execution time. */
	const LOCAL_FIELD_EXECUTION_TIME = 'executionTime';
	/** Field for any additional options. */
	const LOCAL_FIELD_OPTIONS = 'options';
	/** Field for the request parameters. */
	const LOCAL_FIELD_PARAMETERS = 'parameters';
	/** Field for the request method. */
	const LOCAL_FIELD_METHOD = 'method';
	/** Field for the protocol. */
	const LOCAL_FIELD_PROTOCOL = 'protocol';
	/** Field for the request URL. */
	const LOCAL_FIELD_URL = 'url';
	/** Field for the host of the request. May be false if the URL is malformed. */
	const LOCAL_FIELD_HOST = 'host';

	/** HTTP protocol */
	const PROTOCOL_HTTP = 'HTTP';

	/**
	 * Constructor.
	 *
	 * @param string $protocol        The protocol.
	 * @param string $method          The request method (if applicable for the protocol).
	 * @param string $url             The request URL.
	 * @param array  $parameters      Request parameters.
	 * @param array  $headers         Extra headers.
	 * @param array  $options         CURL options.
	 * @param float  $executionTime   The execution time in seconds.
	 *
	 * @internal param string $message The message to display.
	 */
	public function __construct(
		$protocol, $method, $url, array $parameters = array(), array $headers = array(), array $options = array(),
		$executionTime = null
	) {
		$file = null;
		$line = null;
		$trace = debug_backtrace();

		if (isset($trace[1])) {
			$trace[1] += array('file' => null, 'line' => null);
			$file = $trace[1]['file'];
			$line = $trace[1]['line'];
		}

		$this->data = array(
			self::LOCAL_FIELD_PROTOCOL       => $protocol,
			self::LOCAL_FIELD_METHOD         => $method,
			self::LOCAL_FIELD_URL            => $url,
			self::LOCAL_FIELD_HOST           => parse_url($url, PHP_URL_HOST),
			self::LOCAL_FIELD_PARAMETERS     => $parameters,
			self::LOCAL_FIELD_OPTIONS        => $options,
			self::LOCAL_FIELD_EXECUTION_TIME => $executionTime,
			self::FIELD_FILE                 => $file,
			self::FIELD_LINE                 => $line,
		);
	}

	/**
	 * Sets the execution time for the request.
	 *
	 * @param float $time   The time in seconds.
	 *
	 * @return void
	 */
	public function setExecutionTime($time) {
		$this->data[self::LOCAL_FIELD_EXECUTION_TIME] = $time;
	}

	/**
	 * Returns the field definitions as an associative array where the field name is the key,
	 * and the description is the value.
	 *
	 * @return array
	 */
	public function getFieldDefinitions() {
		return array(
			self::LOCAL_FIELD_PROTOCOL       => 'Protocol',
			self::LOCAL_FIELD_METHOD         => 'Method',
			self::LOCAL_FIELD_URL            => 'Request URL',
			self::LOCAL_FIELD_PARAMETERS     => 'Parameters',
			self::LOCAL_FIELD_OPTIONS        => 'Request options',
			self::LOCAL_FIELD_EXECUTION_TIME => 'Execution time',
			self::FIELD_FILE                 => 'File',
			self::FIELD_LINE                 => 'Line',
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
		return self::DEBUG_ITEM_CURL_REQUEST;
	}

	/**
	 * Returns the location ID for the item's source in file @ line format.
	 *
	 * @return string
	 */
	public function getLocationId() {
		return $this->data[self::FIELD_FILE] . ' @ ' . $this->data[self::FIELD_LINE];
	}

}
