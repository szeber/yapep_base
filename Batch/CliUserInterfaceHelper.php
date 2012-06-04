<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Batch
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Batch;
use YapepBase\Exception\Exception;

/**
 * Class for creating help output for batch scripts.
 *
 * Example usage:
 * <code>
 * <?php
 * // Instantiate the helper
 * $helper = new \YapepBase\Batch\CliUserInterfaceHelper('Test script');
 *
 * // Add a usage for displaying the help
 * $helpUsage   = $helper->addUsage('Show the help message');
 *
 * // Add a usage for the example usage.
 * $exampleUsage = $helper->addUsage('Example usage');
 *
 * // Add a switch to the help usage with only a short name
 * $helper->addSwitch('h', null, 'Show the help message', $helpUsage);
 * // Add an optional verbose switch to both usages with both a short and a long switch name
 * $helper->addSwitch('v', 'verbose', 'Be verbose', array($helpUsage, $exampleUsage), true);
 * // Add a switch to the example usage with only a long name and a required parameter
 * $helper->addSwitch(null, 'example', 'The printed example', $exampleUsage, false, 'example');
 *
 * // Retrieve the parsed arguments
 * $args = $helper->getParsedArgs();
 *
 * // Set the verbose option
 * if (isset($args['v']) || isset($args['verbose'])) {
 *     $verbose = true;
 * } else {
 *     $verbose = false;
 * }
 *
 * // If the 'h' switch is set we show the help output.
 * if (isset($args['h'])) {
 *     if ($verbose) {
 *         // Verbose output
 *         echo "Printing help message\n";
 *     }
 *     echo $helper->getUsageOutput(true);
 *     exit;
 * }
 *
 * // The example param is required, if it's not set output the usage of the script with a custom error.
 * if (empty($args['example'])) {
 *     $helper->setErrorMessage('The example parameter is required');
 *     echo $helper->getUsageOutput();
 *     exit;
 * } else {
 *     $example = $args['example'];
 * }
 *
 * // Print final output
 * if ($verbose) {
 *     echo "Printing output\n";
 * }
 * echo $example . "\n";
 * </code>
 * With the -h switch the above script will output:
 * <code>
 * Usages:
 *
 * Show the help message:
 *     example.php  -h  [-v|-verbose]
 *
 * Example usage:
 *     example.php  [-v|-verbose]  -example=<example>
 *
 *
 *
 * Test script
 *
 *   -h                Show the help message
 *   -v, --verbose     Be verbose
 *       --example     The printed example
 * </code>
 *
 * @package    YapepBase
 * @subpackage Batch
 */
class CliUserInterfaceHelper {

	/** Maximum line length */
	const MAX_LINE_LENGTH = 75;

	/** Number of spaces used for 1 level of indentation */
	const INDENT_SPACE_COUNT = 4;

	/** The key of the all usages subarray in the usage switches array. */
	const ALL_USAGE_KEY = 'all';

	/**
	 * Name of the script
	 *
	 * @var string
	 */
	protected $scriptName;

	/**
	 * Error message that should be printed
	 *
	 * @var string
	 */
	protected $errorMessage;

	/**
	 * All of the switches for the script.
	 *
	 * @var array
	 */
	protected $switches = array();

	/**
	 * All usage methods for the script.
	 *
	 * @var array
	 */
	protected $usages = array();

	/**
	 * Switches for each usage.
	 *
	 * @var array
	 */
	protected $usageSwitches = array(self::ALL_USAGE_KEY => array());

	/**
	 * Description of the script.
	 *
	 * @var string
	 */
	protected $description;

	/**
	 * The length of the longest allowed long switch's name in characters.
	 *
	 * @var int
	 */
	protected $longestSwitchNameLength = 0;

	/**
	 * Array containing all the already set short switches.
	 *
	 * @var array
	 */
	protected $shortSwitches = array();

	/**
	 * Array containing all the already set long switches.
	 *
	 * @var array
	 */
	protected $longSwitches = array();

	/**
	 * Constructor.
	 *
	 * @param string $description   Description of the script. All newlines are converted to spaces in it, and multiple
	 *                              spaces are replaced with a single space in it.
	 * @param string $scriptName    Filename of the script.
	 */
	public function __construct($description, $scriptName = null) {
		$this->description = preg_replace('/\s{2,}/', ' ', str_replace(array("\n", "\r"), ' ', trim($description)));
		$this->scriptName = (empty($scriptName) ? basename($_SERVER['argv'][0]) : $scriptName);
	}

	/**
	 * Adds a new usage method to the script.
	 *
	 * @param string $description   Description of the usage method.
	 *
	 * @return int
	 */
	public function addUsage($description) {
		$this->usages[] = $description;
		end($this->usages);
		$usageIndex = key($this->usages);
		$this->usageSwitches[$usageIndex] = array();
		return $usageIndex;
	}

	/**
	 * Adds a new switch to the script.
	 *
	 * @param string    $shortName         Short name of the switch.
	 * @param string    $longName          Long name of the switch.
	 * @param string    $description       Description of the switch.
	 * @param int|array $usageIndexes      An array containing the usage indexes for the switch, or optionally an int
	 *                                     if the switch is only valid for one usage method. If set to NULL, it will be
	 *                                     added to all usages.{@see self::addUsage()}
	 * @param bool      $isOptional        If TRUE, the switch is not required.
	 * @param null      $paramName         Name of the switch parameter to display.
	 * @param bool      $paramIsOptional   If TRUE, the switch parameter is treated as optional.
	 *
	 * @return void
	 *
	 * @throws \YapepBase\Exception\Exception
	 */
	public function addSwitch(
		$shortName, $longName, $description, $usageIndexes, $isOptional = false, $paramName = null,
		$paramIsOptional = false
	) {
		$description = preg_replace('/\s{2,}/', ' ', str_replace(array("\n", "\r"), ' ', trim($description)));

		$switchData = array(
			'shortName'       => $shortName,
			'longName'        => $longName,
			'description'     => $description,
			'isOptional'      => $isOptional,
			'paramName'       => $paramName,
			'paramIsOptional' => $paramIsOptional,
		);

		if (empty($shortName) && empty($longName)) {
			throw new Exception('Neither short, nor long name given for the switch');
		}

		if (!empty($shortName)) {
			if (in_array($shortName, $this->shortSwitches)) {
				throw new Exception('Short switch already added: ' . $shortName);
			} else {
				$this->shortSwitches[] = $shortName;
			}
		}

		if (!empty($longName)) {
			if (in_array($longName, $this->longSwitches)) {
				throw new Exception('Long switch already added: ' . $longName);
			} else {
				$this->longSwitches[] = $longName;
			}
		}

		if (mb_strlen($longName, 'UTF-8') > $this->longestSwitchNameLength) {
			$this->longestSwitchNameLength = mb_strlen($longName, 'UTF-8');
		}

		$this->switches[] = $switchData;
		if (is_null($usageIndexes)) {
			$this->usageSwitches[self::ALL_USAGE_KEY][] = $switchData;
		} elseif (is_array($usageIndexes)) {
			foreach ($usageIndexes as $index) {
				if (!isset($this->usageSwitches[$index])) {
					throw new Exception('The specified usage index is not set: ' . $index);
				}
				$this->usageSwitches[$index][] = $switchData;
			}
		} elseif (is_numeric($usageIndexes) && isset($this->usageSwitches[$usageIndexes])) {
			$this->usageSwitches[$usageIndexes][] = $switchData;
		} else {
			throw new Exception('The specified usage index is not set: ' . $usageIndexes);
		}
	}

	/**
	 * Sets the error message
	 *
	 * @param string $message   The error message.
	 *
	 * @return void
	 */
	public function setErrorMessage($message) {
		$this->errorMessage = $message;
	}

	/**
	 * Returns the usage output, optionally with the help message.
	 *
	 * @param bool $showHelp   If TRUE, the usage output will also contain the help (switch list and explanation).
	 *
	 * @return string   The formatted usage output.
	 *
	 * @throws \YapepBase\Exception\Exception   If no usages are defined for the script.
	 */
	public function getUsageOutput($showHelp = false) {
		if (empty($this->usages)) {
			throw new Exception('Tried to get usage output without any defined usages');
		}
		$message = '';
		if (!$showHelp) {
			$errorLine = $this->getIndentedBlock(
				(empty($this->errorMessage) ? 'A required parameter is missing.' : $this->errorMessage),
				1
			);
			$message .= "Error:\n" . $errorLine . "\n\n";
		}
		$message .= (count($this->usages) > 1 ? 'Usages:' : 'Usage:') . "\n\n";
		foreach ($this->usages as $index => $usage) {
			$message .= $this->getUsageWithSwitches($usage,
				array_merge($this->usageSwitches[self::ALL_USAGE_KEY], $this->usageSwitches[$index]));
		}

		if ($showHelp) {
			$message .= $this->getHelp();
		}

		return $message;
	}

	/**
	 * Returns the formatted help output (script description, switch list with explanation)
	 *
	 * @return string
	 */
	protected function getHelp() {
		$message = "\n\n" . $this->getIndentedBlock($this->description, 0) . "\n";
		$indentCount = ceil((10 + $this->longestSwitchNameLength) / self::INDENT_SPACE_COUNT);
		$linePad = $indentCount * self::INDENT_SPACE_COUNT;

		if (!empty($this->switches)) {
			foreach ($this->switches as $switchData) {

				$shortName = (empty($switchData['shortName']) ? '  ' : '-' . $switchData['shortName']);

				$switchLine = str_pad('  ' . $shortName
					. (empty($switchData['shortName']) || empty($switchData['longName']) ? '  ' : ', ')
					. (empty($switchData['longName']) ? '' : '--'
					. str_pad($switchData['longName'], $this->longestSwitchNameLength, ' ', STR_PAD_RIGHT)),
					$linePad, ' ', STR_PAD_RIGHT) . $switchData['description'];

				$fistSwitchLine = $this->getWrappedString($switchLine, $linePad);

				if (strlen($switchLine)) {
					$fistSwitchLine .= "\n" . $this->getIndentedBlock($switchLine, $indentCount);
				} else {
					$fistSwitchLine .= "\n";
				}
				$message .= $fistSwitchLine;
			}
		}
		return $message . "\n\n";
	}

	/**
	 * Returns a formatted usage output with the switches that are valid for the usage.
	 *
	 * @param string $description   Description of the usage method.
	 * @param array  $switches      Array containing all the switches that are valid for the given usage.
	 *
	 * @return string
	 */
	protected function getUsageWithSwitches($description, array $switches) {
		$commandFormat = $this->scriptName;
		foreach ($switches as $switchData) {
			$commandFormat .= ' ';

			$switchVersions = array();
			if (!empty($switchData['shortName'])) {
				$switchVersion = '-' . $switchData['shortName'];
				if (!empty($switchData['paramName'])) {
					$switchVersion .= (
						$switchData['paramIsOptional']
						? '[=<' . $switchData['paramName'] . '>]'
						: '=<' . $switchData['paramName'] . '>'
					);
				}
				$switchVersions[] = $switchVersion;
			}
			if (!empty($switchData['longName'])) {
				$switchVersion = '--' . $switchData['longName'];
				if (!empty($switchData['paramName'])) {
					$switchVersion .= (
					$switchData['paramIsOptional']
						? '[=<' . $switchData['paramName'] . '>]'
						: '=<' . $switchData['paramName'] . '>'
					);
				}
				$switchVersions[] = $switchVersion;
			}

			$commandFormat .= ' ' . ($switchData['isOptional'] ? '[' : '') . implode('|', $switchVersions)
				. ($switchData['isOptional'] ? ']' : '');
		}
		return $this->getIndentedBlock($description . ':', 1) . $this->getIndentedBlock($commandFormat, 2) . "\n";
	}

	/**
	 * Returns the provided string indented by the specified amount and wrapped to the max line length.
	 *
	 * @param string $string     The string to format.
	 * @param int    $indentBy   The number of indentation blocks to indent by. {@see self::INDENT_SPACE_COUNT}
	 *
	 * @return string
	 */
	protected function getIndentedBlock($string, $indentBy = 1) {
		$indentLength = $indentBy * self::INDENT_SPACE_COUNT;
		$string = trim($string);
		$lines = array();
		while (strlen($string) > 0) {
			$string = str_pad('', $indentLength, ' ') . $string;
			$lines[] = $this->getWrappedString($string, $indentLength + 5);
		}
		return implode("\n", $lines) . "\n";
	}

	/**
	 * Returns the provided string wrapped to the maximum line length.
	 *
	 * @param string $string        The string to format.
	 * @param int    $minSpacePos   If the last space in the line would occur before this character, the line will
	 *                              not be wrapped at a space, but exactly at the maximum line length.
	 *
	 * @return string
	 */
	protected function getWrappedString(&$string, $minSpacePos = 0) {
		$line = mb_substr($string, 0, (self::MAX_LINE_LENGTH + 1), 'UTF-8');
		$lastSpace = mb_strrpos($line, ' ', null, 'UTF-8');
		if (mb_strlen($line, 'UTF-8') <= self::MAX_LINE_LENGTH || $lastSpace <= $minSpacePos) {
			$line = rtrim(mb_substr($line, 0, self::MAX_LINE_LENGTH, 'UTF-8'));
		} else {
			$line = rtrim(mb_substr($line, 0, $lastSpace, 'UTF-8'));
		}
		$string = trim(mb_substr($string, mb_strlen($line, 'UTF-8'),
			mb_strlen($string, 'UTF-8'), 'UTF-8'));
		return $line;
	}

	/**
	 * Returns the short option list in getopt's format for all defined switches.
	 *
	 * @return string
	 */
	protected function getGetoptShortList() {
		$switches = '';
		foreach ($this->switches as $switchData) {
			if (!empty($switchData['shortName'])) {
				$switches .= $switchData['shortName'];
				if (!empty($switchData['paramName'])) {
					$switches .= ($switchData['paramIsOptional'] ? '::' : ':');
				}
			}
		}
		return $switches;
	}

	/**
	 * Returns the long option list in getopt's format for all defined switches.
	 *
	 * @return string
	 */
	protected function getGetoptLongList() {
		$switches = array();
		foreach ($this->switches as $switch) {
			if (!empty($switch['longName'])) {
				$name = $switch['longName'];
				if (!empty($switch['paramName'])) {
					$name .= ($switch['paramIsOptional'] ? '::' : ':');
				}
				$switches[] = $name;
			}
		}
		return $switches;
	}

	/**
	 * Returns the arguments that have been parsed by getopt.
	 *
	 * Be careful, that any not defined input in the script arguments will stop parsing any further arguments.
	 * {@see http://php.net/getopt}
	 *
	 * @return array
	 */
	public function getParsedArgs() {
		return getopt($this->getGetoptShortList(), $this->getGetoptLongList());
	}
}