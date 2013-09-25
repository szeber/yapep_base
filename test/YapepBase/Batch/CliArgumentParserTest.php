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
 * Test for the CliArgumentParser class.
 *
 * @package    YapepBase
 * @subpackage Batch
 */
class CliArgumentParserTest extends \PHPUnit_Framework_TestCase {

	/**
	 * Tests the error handling during the setup phase.
	 *
	 * @return void
	 */
	public function testSetupErrorHandling() {
		$parser = new CliArgumentParser();
		try {
			$parser->addSwitch('', '');
			$this->fail(
				'A ParameterException should be thrown when trying to add a switch without both a short and a long name');
		} catch (ParameterException $e) {
			// Do nothing
		}
	}

	/**
	 * Tests the error handling during parsing.
	 *
	 * @return void
	 */
	public function testParseErrorHandling() {
		$parser = new CliArgumentParser();
		$parser->addSwitch('a', '', true);
		$parser->addSwitch('b', '');

		try {
			$parser->parse(array('-a', '-b'));
			$this->fail('Parsing should fail when a switch that requires a value does not have one');
		}
		catch (Exception $e) {
			// Do nothing
		}

		try {
			$parser->parse(array('-a'));
			$this->fail('Parsing should fail when a switch that requires a value does not have one');
		}
		catch (Exception $e) {
			// Do nothing
		}

		try {
			$parser->parse(array('-v'));
			$this->fail('Parsing should fail when an unknown switch is passed');
		}
		catch (Exception $e) {
			// Do nothing
		}
	}

	/**
	 * Tests the parsing functions.
	 *
	 * @return void
	 */
	public function testParsing() {
		$parser = new CliArgumentParser();
		$parser->addSwitch('a', 'switch-a');
		$parser->addSwitch('b', null);
		$parser->addSwitch(null, 'switch-c');
		$parser->addSwitch('v', 'value', true);
		$parser->addSwitch('o', 'optional', true, true);

		// Test parsing for no arguments
		$parser->parse(array());
		$this->assertSame(array(), $parser->getParsedSwitches(), 'Invalid switches parsed for empty arg list');
		$this->assertSame(array(), $parser->getParsedOperands(), 'Invalid operands parsed for empty arg list');

		// Test parsing for simple switches ('-a -b --switch-c')
		$expectedSwitches = array(
			'a'        => 1,
			'switch-a' => 1,
			'b'        => 1,
			'switch-c' => 1
		);
		ksort($expectedSwitches);

		$expectedOperands = array();

		$parser->parse(array(
			'-a',
			'-b',
			'--switch-c',
		));
		$switches = $parser->getParsedSwitches();
		ksort($switches);

		$this->assertSame($expectedSwitches, $switches, 'Invalid switches were parsed');
		$this->assertSame($expectedOperands, $parser->getParsedOperands(), 'Invalid operands were parsed');

		// Test parsing for value switches without a separator ('-vvalue1 -ovalue2')
		$expectedSwitches = array(
			'v'        => 'value1',
			'value'    => 'value1',
			'o'        => 'value2',
			'optional' => 'value2'
		);
		ksort($expectedSwitches);

		$expectedOperands = array();

		$parser->parse(array(
			'-vvalue1',
			'-ovalue2',
		));
		$switches = $parser->getParsedSwitches();
		ksort($switches);

		$this->assertSame($expectedSwitches, $switches, 'Invalid switches were parsed');
		$this->assertSame($expectedOperands, $parser->getParsedOperands(), 'Invalid operands were parsed');

		// Test parsing for value switches with = as separator for short versions ('-v=value1 -o=value2')
		$expectedSwitches = array(
			'v'        => 'value1',
			'value'    => 'value1',
			'o'        => 'value2',
			'optional' => 'value2'
		);
		ksort($expectedSwitches);

		$expectedOperands = array();

		$parser->parse(array(
			'-v=value1',
			'-o=value2',
		));
		$switches = $parser->getParsedSwitches();
		ksort($switches);

		$this->assertSame($expectedSwitches, $switches, 'Invalid switches were parsed');
		$this->assertSame($expectedOperands, $parser->getParsedOperands(), 'Invalid operands were parsed');

		// Test parsing for value switches with space separator for short versions ('-v value1')
		$expectedSwitches = array(
			'v'        => 'value1',
			'value'    => 'value1',
		);
		ksort($expectedSwitches);

		$expectedOperands = array();

		$parser->parse(array(
			'-v',
			'value1',
		));
		$switches = $parser->getParsedSwitches();
		ksort($switches);

		$this->assertSame($expectedSwitches, $switches, 'Invalid switches were parsed');
		$this->assertSame($expectedOperands, $parser->getParsedOperands(), 'Invalid operands were parsed');

		// Test parsing for value switches with = as separator for short versions ('--value=value1 --optional=value2')
		$expectedSwitches = array(
			'v'        => 'value1',
			'value'    => 'value1',
			'o'        => 'value2',
			'optional' => 'value2'
		);
		ksort($expectedSwitches);

		$expectedOperands = array();

		$parser->parse(array(
			'--value=value1',
			'--optional=value2',
		));
		$switches = $parser->getParsedSwitches();
		ksort($switches);

		$this->assertSame($expectedSwitches, $switches, 'Invalid switches were parsed');
		$this->assertSame($expectedOperands, $parser->getParsedOperands(), 'Invalid operands were parsed');

		// Test parsing for value switches with space separator for short versions ('--value value1')
		$expectedSwitches = array(
			'v'        => 'value1',
			'value'    => 'value1',
		);
		ksort($expectedSwitches);

		$expectedOperands = array();

		$parser->parse(array(
			'--value',
			'value1',
		));
		$switches = $parser->getParsedSwitches();
		ksort($switches);

		$this->assertSame($expectedSwitches, $switches, 'Invalid switches were parsed');
		$this->assertSame($expectedOperands, $parser->getParsedOperands(), 'Invalid operands were parsed');

		// Test parsing for collapsed syntax, with one value switch ('-abvvalue1')
		$expectedSwitches = array(
			'a'        => 1,
			'switch-a' => 1,
			'b'        => 1,
			'v'        => 'value1',
			'value'    => 'value1',
		);
		ksort($expectedSwitches);

		$expectedOperands = array();

		$parser->parse(array(
			'-abvvalue1',
		));
		$switches = $parser->getParsedSwitches();
		ksort($switches);

		$this->assertSame($expectedSwitches, $switches, 'Invalid switches were parsed');
		$this->assertSame($expectedOperands, $parser->getParsedOperands(), 'Invalid operands were parsed');

		// Test parsing for collapsed syntax for non-value switches with multiple invocations ('-aaba')
		$expectedSwitches = array(
			'a'        => 3,
			'switch-a' => 3,
			'b'        => 1,
		);
		ksort($expectedSwitches);

		$expectedOperands = array();

		$parser->parse(array(
			'-aaba',
		));
		$switches = $parser->getParsedSwitches();
		ksort($switches);

		$this->assertSame($expectedSwitches, $switches, 'Invalid switches were parsed');
		$this->assertSame($expectedOperands, $parser->getParsedOperands(), 'Invalid operands were parsed');

		// Test parsing for operands ('-b test1, test2')
		$expectedSwitches = array(
			'b' => 1,
		);
		ksort($expectedSwitches);

		$expectedOperands = array(
			'test1',
			'test2',
		);

		$parser->parse(array(
			'-b',
			'test1',
			'test2',
		));
		$switches = $parser->getParsedSwitches();
		ksort($switches);

		$this->assertSame($expectedSwitches, $switches, 'Invalid switches were parsed');
		$this->assertSame($expectedOperands, $parser->getParsedOperands(), 'Invalid operands were parsed');

		// Test parsing for finish operator ('-a -- -b')
		$expectedSwitches = array(
			'a'        => 1,
			'switch-a' => 1,
		);
		ksort($expectedSwitches);

		$expectedOperands = array(
			'-b'
		);

		$parser->parse(array(
			'-a',
			'--',
			'-b',
		));
		$switches = $parser->getParsedSwitches();
		ksort($switches);

		$this->assertSame($expectedSwitches, $switches, 'Invalid switches were parsed');
		$this->assertSame($expectedOperands, $parser->getParsedOperands(), 'Invalid operands were parsed');

		// Test parsing for optional value and operand ('-o test')
		$expectedSwitches = array(
			'o'        => false,
			'optional' => false
		);
		ksort($expectedSwitches);

		$expectedOperands = array(
			'test'
		);

		$parser->parse(array(
			'-o',
			'test'
		));
		$switches = $parser->getParsedSwitches();
		ksort($switches);

		$this->assertSame($expectedSwitches, $switches, 'Invalid switches were parsed');
		$this->assertSame($expectedOperands, $parser->getParsedOperands(), 'Invalid operands were parsed');

		// Test parsing when the same value switch is added multiple times ('-vvalue1 -vvalue2')
		$expectedSwitches = array(
			'v'        => 'value2',
			'value'    => 'value2',
		);
		ksort($expectedSwitches);

		$expectedOperands = array();

		$parser->parse(array(
			'-vvalue1',
			'-vvalue2',
		));
		$switches = $parser->getParsedSwitches();
		ksort($switches);

		$this->assertSame($expectedSwitches, $switches, 'Invalid switches were parsed');
		$this->assertSame($expectedOperands, $parser->getParsedOperands(), 'Invalid operands were parsed');
	}
}
