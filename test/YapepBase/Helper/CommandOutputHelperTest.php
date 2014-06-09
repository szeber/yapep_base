<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBase
 * @subpackage Helper
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Helper;


use YapepBase\Config;
use YapepBase\File\FileHandlerPhp;
use YapepBase\Shell\CommandExecutor;
use YapepBase\Shell\CommandOutput;

/**
 * Test class for CommandOutputHelper.
 *
 * @package    YapepBase
 * @subpackage Helper
 */
class CommandOutputHelperTest extends \YapepBase\BaseTest {

	/**
	 * The test file path for the runCommandWithStdErr method
	 *
	 * @var string
	 */
	public $testFilePath;

	/**
	 * The CommandOutputHelper object.
	 *
	 * @var CommandOutputHelper;
	 */
	protected $commandOutputHelper;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp() {
		parent::setUp();

		$this->commandOutputHelper = new CommandOutputHelper();
	}

	protected function tearDown() {
		parent::tearDown();
		$testPath = getenv('YAPEPBASE_TEST_TEMPPATH');

		if (empty($testPath)) {
			return;
		}

		if (file_exists($testPath)) {
			$fileHandler = new FileHandlerPhp();

			$fileHandler->removeDirectory($testPath, true);
		}
	}

	/**
	 * Tests the runCommandWithStdErr() method.
	 *
	 * @return void
	 */
	public function testRunCommandWithStdErr() {
		$testPath = getenv('YAPEPBASE_TEST_TEMPPATH');
		$this->testFilePath = null;
		$testInstance  = $this;
		$expectedError = 'error message';

		$commandExecutorMock = \Mockery::mock('\YapepBase\Shell\CommandExecutor')
			->shouldReceive('setOutputRedirection')
			->with(CommandExecutor::OUTPUT_REDIRECT_STDERR, \Mockery::on(function ($param) use ($testInstance) {
				$testInstance->testFilePath = $param;
				return true;
			}))
			->getMock()
			->shouldReceive('run')
			->andReturnUsing(function() use ($testInstance, $expectedError) {
				file_put_contents($testInstance->testFilePath, $expectedError);
				return new CommandOutput('test', '', 0, 'test 2> ' . $testInstance->testFilePath);
			})
			->getMock();

		if (empty($testPath)) {
			$this->markTestSkipped('Test cannot be done without a test directory');
		}

		if (!file_exists($testPath) && !mkdir($testPath, 0755, true)) {
			$this->fail('The test path does not exist, and cannot be created: ' . $testPath);
		}

		Config::getInstance()->set('system.commandOutputHelper.work.path', $testPath);

		$this->commandOutputHelper->runCommandWithStdErr($commandExecutorMock, $errorMessage);

		$this->assertEquals($expectedError, $errorMessage);
		$this->assertFileNotExists($this->testFilePath);
	}
}