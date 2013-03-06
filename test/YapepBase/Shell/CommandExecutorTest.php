<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBase
 * @subpackage Shell
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Shell;


/**
 * Test for the CommandExecutor class
 *
 * @package    YapepBase
 * @subpackage Shell
 */
class CommandExecutorTest extends \PHPUnit_Framework_TestCase {

	/**
	 * Tests the full command creation method.
	 */
	public function testGetCommand() {
		$expectedCommand = '/test';
		$expectedParams = array(
			'-v' => null,
			'-t' => 'testValue',
			null => 'testArgument',
		);

		// Test generating the command with the default separator
		$command = new CommandExecutor();
		$command->setCommand($expectedCommand);

		foreach ($expectedParams as $name => $value) {
			$command->addParam($name, $value);
		}

		$expectedOutput = escapeshellarg($expectedCommand) . ' -v -t ' . escapeshellarg('testValue') . ' '
			. escapeshellarg('testArgument');

		$this->assertSame($expectedOutput, $command->getCommand());

		// Test generating the command with an empty separator
		$command = new CommandExecutor();
		$command->setCommand($expectedCommand);

		foreach ($expectedParams as $name => $value) {
			$command->addParam($name, $value);
		}

		$command->setSwitchValueSeparator('');

		$expectedOutput = escapeshellarg($expectedCommand) . ' -v -t' . escapeshellarg('testValue') . ' '
			. escapeshellarg('testArgument');

		$this->assertSame($expectedOutput, $command->getCommand());

		// Test generating the command with '=' as the separator
		$command = new CommandExecutor();
		$command->setCommand($expectedCommand);

		foreach ($expectedParams as $name => $value) {
			$command->addParam($name, $value);
		}

		$command->setSwitchValueSeparator('=');

		$expectedOutput = escapeshellarg($expectedCommand) . ' -v -t=' . escapeshellarg('testValue') . ' '
			. escapeshellarg('testArgument');

		$this->assertSame($expectedOutput, $command->getCommand());

		// Test with multiple arguments
		$command = new CommandExecutor();
		$command->setCommand($expectedCommand);

		$command->addParam(null, 'testArgument1');
		$command->addParam(null, 'testArgument 2');

		$expectedOutput = escapeshellarg($expectedCommand) .' ' . escapeshellarg('testArgument1') . ' '
			. escapeshellarg('testArgument 2');

		$this->assertSame($expectedOutput, $command->getCommand());

		// Test with multiple instances of the same switch
		$command = new CommandExecutor();
		$command->setCommand($expectedCommand);

		$command->addParam('-v');
		$command->addParam('-v');
		$command->addParam('-v');

		$expectedOutput = escapeshellarg($expectedCommand) .' -v -v -v';

		$this->assertSame($expectedOutput, $command->getCommand());

		// Test with chained commands
		$command1 = new CommandExecutor('test1');
		$command2 = new CommandExecutor('test2');
		$command3 = new CommandExecutor('test3');

		$command1->setCommand('test1')->setChainedCommand($command2, CommandExecutor::OPERATOR_PIPE);
		$command2->setCommand('test2')->setChainedCommand($command3, CommandExecutor::OPERATOR_BINARY_AND);
		$command3->setCommand('test3');

		$expectedOutput = escapeshellarg('test1') . ' | ' . escapeshellarg('test2') . ' && ' . escapeshellarg('test3');

		$this->assertSame($expectedOutput, $command1->getCommand(), 'The generated chained command is invalid');
	}
}
