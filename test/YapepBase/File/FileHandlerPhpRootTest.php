<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBase
 * @subpackage File
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\File;


/**
 * Tests for the FileHandlerPhp class that must be run as root.
 *
 * Care must be taken when running tests as root, since a mistake can make serious damages to the system
 * running the tests. Because of this, it's not recommended to run these tests.
 *
 * @package    YapepBase
 * @subpackage File
 */
class FileHandlerPhpRootTest extends \PHPUnit_Framework_TestCase {

	/**
	 * The FileHandler object.
	 *
	 *
	 * @var FileHandlerPhp;
	 */
	protected $fileHandler;

	/**
	 * Contains all the files and directories to remove after the end of a test.
	 *
	 * Add here all the FS resources you are creating in a test.
	 *
	 * @var array
	 */
	protected $resourcesToRemove = array(
		'test.txt',
		'test1.txt',
		'test.php',
	);

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp() {
		parent::setUp();

		clearstatcache();

		// All tests in this class can only be run as root.
		if (!function_exists('posix_getuid') || 0 != posix_getuid()) {
			$this->markTestSkipped('This test may only be run as root on a unix filesystem with the posix extension');
		}

		$this->fileHandler = new FileHandlerPhp();

		try {
			$this->cleanupTestDirectory();
		}
		catch (\PHPUnit_Framework_SkippedTestError $e) {
		}
		catch (\PHPUnit_Framework_AssertionFailedError $e) {
		}
	}

	/**
	 * Tears down the fixture, for example, close a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 */
	protected function tearDown() {
		parent::tearDown();
		$this->cleanupTestDirectory();
	}

	/**
	 * Cleans up the test directory.
	 *
	 * @return void
	 */
	protected function cleanupTestDirectory() {
		$testPath = $this->getTestPath();
		foreach ($this->resourcesToRemove as $file) {
			if (file_exists($testPath . $file)) {
				unlink($testPath . $file);
			}
		}

		foreach ($this->resourcesToRemove as $file) {
			if (file_exists($testPath . $file)) {
				rmdir($testPath . $file);
			}
		}
	}

	/**
	 * Returns the test path.
	 *
	 * @throws \PHPUnit_Framework_SkippedTestError       If the test path has not been set.
	 * @throws \PHPUnit_Framework_AssertionFailedError   If the set path does not exist, and cannot be created.
	 *
	 * @return string   The test path.
	 */
	protected function getTestPath() {
		$testPath = getenv('YAPEPBASE_TEST_TEMPPATH');

		if (empty($testPath)) {
			$this->markTestSkipped('Test cannot be done without a test directory');
		}

		if (!file_exists($testPath) && !mkdir($testPath, 0755, true)) {
			$this->fail('The given path does not exist, and cannot be created: ' . $testPath);
		}

		return rtrim($testPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
	}

	/**
	 * Tests the changeOwner() method.
	 *
	 * @return void
	 */
	public function testChangeOwner() {
		if (!function_exists('posix_getpwuid') || false === posix_getpwuid(1)) {
			$this->markTestSkipped(
				'Either the posix_getpwuid() function is not available, or there is no user with UID of 1');
		}

		if (!function_exists('posix_getgrgid') || false === posix_getgrgid(1)) {
			$this->markTestSkipped(
				'Either the posix_getgrgid() function is not available, or there is no group with GID of 1');
		}

		$file = 'test.txt';
		$fullPath = $this->getTestPath() . $file;

		touch($fullPath);

		$this->assertTrue(file_exists($fullPath), 'Precondition failed. Unable to create test file: ' . $fullPath);
		$this->assertEquals(0, fileowner($fullPath), 'Precondition failed. Invalid owner for new file.');

		clearstatcache();

		$this->fileHandler->changeOwner($fullPath, null, 1);
		$this->assertEquals(1, fileowner($fullPath), 'Failed to change owner of the test file');

		clearstatcache();

		$this->fileHandler->changeOwner($fullPath, 1, 1);
		$this->assertEquals(1, filegroup($fullPath), 'Failed to change group of the test file');
		$this->assertEquals(1, fileowner($fullPath), 'Invalid user set for file when changing both group and user');

		clearstatcache();

		$this->fileHandler->changeOwner($fullPath, 0);
		$this->assertEquals(0, filegroup($fullPath), 'Failed to change group of the test file');
		$this->assertEquals(1, fileowner($fullPath), 'Changing only the group should not change the owner of a file');
	}


}
