<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBase
 * @subpackage Batch
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Batch;
use YapepBase\Exception\Exception;
use YapepBase\Exception\ParameterException;

/**
 * Parser to parse CLI arguments and switches.
 *
 * This class implements a functionality similar to getopt().
 * All switches can have either a short or a long name, or both. If both are set, the parsed array will contain the
 * same values for both the short and the long name. A switch can have a required or optional value. If a switch has
 * an optional value, and the value is not provided, the parsed value for it will be FALSE. Value switches can be
 * defined multiple times, the value of the last instance overwrites any earlier values. For non-value switches,
 * the parsed value will contain how many times they were specified.
 *
 * @package    YapepBase
 * @subpackage Batch
 */
class CliArgumentParser {

	/** Finish token. */
	const TOKEN_FINISH = 'finish';
	/** Long switch token. */
	const TOKEN_LONG_SWITCH = 'longSwitch';
	/** Short switch token. */
	const TOKEN_SHORT_SWITCH = 'shortSwitch';
	/** Value token. */
	const TOKEN_VALUE = 'value';

	/** Normal parsing mode. */
	const PARSE_MODE_NORMAL = 'normal';
	/** Value parsing mode. */
	const PARSE_MODE_VALUE = 'value';

	/**
	 * Contains the configured switches.
	 *
	 * @var array
	 */
	protected $configuredSwitches = array();

	/**
	 * Contains the already parsed switches.
	 *
	 * @var array
	 */
	protected $parsedSwitches = array();

	/**
	 * Contains the already parsed operands.
	 *
	 * @var array
	 */
	protected $parsedOperands = array();

	/**
	 * Adds a switch to the list of known switches.
	 *
	 * @param string $shortName         The short name of the switch.
	 * @param string $longName          The long name of the switch.
	 * @param bool   $hasValue          If TRUE, the switch has a value.
	 * @param bool   $valueIsOptional   If TRUE, the value is treated as optional.
	 *
	 * @return void
	 *
	 * @throws \YapepBase\Exception\ParameterException   If the switch has neither a short, nor a long name.
	 */
	public function addSwitch($shortName, $longName, $hasValue = false, $valueIsOptional = false) {
		$switchData = array(
			'shortName'       => $shortName,
			'longName'        => $longName,
			'hasValue'        => $hasValue,
			'valueIsOptional' => $valueIsOptional,
		);

		if (empty($shortName) && empty($longName)) {
			throw new ParameterException('Neither short, nor long name given for the switch');
		}

		if (!empty($shortName)) {
			$this->configuredSwitches[$shortName] = $switchData;
		}

		if (!empty($longName)) {
			$this->configuredSwitches[$longName] = $switchData;
		}
	}

	/**
	 * Parses the provided arguments, or if none are provided takes the ones from the currently running script.
	 *
	 * @param array $arguments   The arguments to parse.
	 *
	 * @return void
	 *
	 * @throws \YapepBase\Exception\Exception   If there is a problem while parsing.
	 */
	public function parse(array $arguments = null) {
		if (is_null($arguments)) {
			$arguments = $_SERVER['argv'];
			array_shift($arguments);
		}

		// Set initial state
		$this->parsedSwitches = array();
		$this->parsedOperands = array();
		$currentMode          = self::PARSE_MODE_NORMAL;
		$currentSwitchName    = null;
		$currentSwitchData    = array();

		while (!empty($arguments)) {
			// Take the first argument from the argument list
			$argument = array_shift($arguments);
			$type = $this->getArgumentTokenType($argument);
			switch ($currentMode) {
				case self::PARSE_MODE_NORMAL:
					// Normal parsing mode, here we expect to find switches, or we stop parsing and treat
					// the remaining args as operands
					switch ($type) {
						case self::TOKEN_VALUE:
							// We found an operand, this means there are no more switches.
							// Re-add the value to the argument list and finish parsing.
							array_unshift($arguments, $argument);
							break 3;

						case self::TOKEN_FINISH:
							// Parsing finished
							break 3;

						case self::TOKEN_SHORT_SWITCH:
							// We found a short switch
							$switchParts = str_split(substr($argument, 1));

							while (!empty($switchParts)) {
								$currentSwitch = array_shift($switchParts);
								$currentSwitchName = '-' . $currentSwitch;
								if (!isset($this->configuredSwitches[$currentSwitch])) {
									throw new Exception('Unknown switch: "' . $currentSwitchName . '"');
								}

								$currentSwitchData = $this->configuredSwitches[$currentSwitch];
								if ($currentSwitchData['hasValue']) {
									// This switch takes a value, no more switches are allowed in this argument
									if (empty($switchParts)) {
										// No value was provided in this argument
										if ($currentSwitchData['valueIsOptional']) {
											// No optional value provided, set it as FALSE
											$this->addParsedSwitch($currentSwitchData, false);
										} else {
											// We expect a value in the next argument
											$currentMode = self::PARSE_MODE_VALUE;
										}
									} else {
										// The rest of the argument is assumed to be the value for the current switch
										if ('=' == reset($switchParts)) {
											// Remove the = sign from the value
											array_shift($switchParts);
										}
										$this->addParsedSwitch($currentSwitchData, implode('', $switchParts));
										$switchParts = array();
									}
								} else {
									// The switch takes no value, add, then continue parsing this arg for more switches
									$this->addParsedSwitch($currentSwitchData);
								}
							}
							break;

						case self::TOKEN_LONG_SWITCH:
							// We found a long switch, explode it by the = sign
							$switchParts = explode('=', substr($argument, 2), 2);
							$currentSwitch = $switchParts[0];
							$currentSwitchName = '--' . $currentSwitch;
							if (!isset($this->configuredSwitches[$currentSwitch])) {
								throw new Exception('Unknown switch: "' . $currentSwitchName . '"');
							}

							$currentSwitchData = $this->configuredSwitches[$currentSwitch];
							if ($currentSwitchData['hasValue']) {
								// This switch takes a value, we check if it had a value separated by the = sign
								if (!isset($switchParts[1])) {
									// No value was provided separated by an = sign
									if ($currentSwitchData['valueIsOptional']) {
										// No optional value provided, set it as FALSE
										$this->addParsedSwitch($currentSwitchData, false);
									} else {
										// We expect a value in the next argument
										$currentMode = self::PARSE_MODE_VALUE;
									}
								} else {
									// There was a value separated from the switch by an = sign
									$this->addParsedSwitch($currentSwitchData, $switchParts[1]);
								}
							} else {
								// The switch takes no value, add it
								$this->addParsedSwitch($currentSwitchData);
							}
							break;
					}
					break;

				case self::PARSE_MODE_VALUE:
					// Value parsing mode, here we only expect to find values for a switch that requires it.
					// If we find it, we switch back to normal mode, or if we find anything else that's an error,
					if ($type == self::TOKEN_VALUE) {
						// We found the value for the switch, so add it, and switch back to normal mode
						$this->addParsedSwitch($currentSwitchData, $argument);
						$currentMode = self::PARSE_MODE_NORMAL;
					} else {
						// We found something else, so the required value is not found for the current switch
						throw new Exception('Value is required, but not provided for the "' . $currentSwitchName
							. '" switch');
					}
					break;
			}
		}

		// Verify, that we exited the parsing in a valid mode
		if ($currentMode == self::PARSE_MODE_VALUE) {
			throw new Exception('Value is required, but not provided for the "' . $currentSwitchName
			. '" switch');
		}

		// Set the operands
		$this->parsedOperands = $arguments;
	}

	/**
	 * Returns the parsed switches.
	 *
	 * @return array
	 */
	public function getParsedSwitches() {
		return $this->parsedSwitches;
	}

	/**
	 * Returns the parsed operands.
	 *
	 * @return array
	 */
	public function getParsedOperands() {
		return $this->parsedOperands;
	}

	/**
	 * Adds a switch to the list of parsed switches.
	 *
	 * @param array  $switchData   The configured data for the switch, that is added.
	 * @param string $value        The value for the switch.
	 *
	 * @return void
	 */
	protected function addParsedSwitch(array $switchData, $value = null) {
		if ($switchData['hasValue']) {
			if (!empty($switchData['shortName'])) {
				$this->parsedSwitches[$switchData['shortName']] = $value;
			}
			if (!empty($switchData['longName'])) {
				$this->parsedSwitches[$switchData['longName']] = $value;
			}
		}
		else {
			if (!empty($switchData['shortName'])) {
				if (isset($this->parsedSwitches[$switchData['shortName']])) {
					$this->parsedSwitches[$switchData['shortName']]++;
				} else {
					$this->parsedSwitches[$switchData['shortName']] = 1;
				}
			}
			if (!empty($switchData['longName'])) {
				if (isset($this->parsedSwitches[$switchData['longName']])) {
					$this->parsedSwitches[$switchData['longName']]++;
				} else {
					$this->parsedSwitches[$switchData['longName']] = 1;
				}
			}
		}
	}

	/**
	 * Returns the token type of the argument.
	 *
	 * @param string $argument   The argument to identify the token type of.
	 *
	 * @return string   {@uses self::TOKEN_*}
	 */
	protected function getArgumentTokenType($argument) {
		if ('--' == $argument) {
			return self::TOKEN_FINISH;
		} elseif (preg_match('#^--[-a-zA-Z0-9]+(=|$)#', $argument)) {
			return self::TOKEN_LONG_SWITCH;
		} elseif (preg_match('#^-[a-zA-Z0-9]#', $argument)) {
			return self::TOKEN_SHORT_SWITCH;
		} else {
			return self::TOKEN_VALUE;
		}
	}


}
