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
 * Tests for the FileHandlerUnix class that must be run as root.
 *
 * Care must be taken when running tests as root, since a mistake can make serious damages to the system
 * running the tests. Because of this, it's not recommended to run these tests.
 *
 * @package    YapepBase
 * @subpackage File
 */
class FileHandlerUnixRootTest extends \PHPUnit_Framework_TestCase {

	/**
	 * The FileHandler object.
	 *
	 *
	 * @var FileHandlerUnix;
	 */
	protected $fileHandler;

	/**
	 * Contains all the files and directories to remove after the end of a test.
	 *
	 * Add here all the file resources you are creating in a test.
	 *
	 * @var array
	 */
	protected $filesToRemove = array(
		'test.txt',
		'test1.txt',
		'test.php',
		'testDir/testFile',
	);

	/**
	 * Contains all the directories to remove after the end of a test.
	 *
	 * Add here all the directory resources you are creating in a test.
	 * They will be deleted in the order they are specified here, and they must be empty when deleting.
	 *
	 * @var array
	 */
	protected $directoriesToRemove = array(
		'testDir',
	);

	/**
	 * The base directory that should be used when testing.
	 *
	 * @var string
	 */
	protected $testBasePath;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp() {
		parent::setUp();

		// All tests in this class can only be run as root.
		if (!function_exists('posix_getuid') || 0 != posix_getuid()) {
			$this->markTestSkipped('This test may only be run as root on a unix filesystem with the posix extension');
		}

		clearstatcache();

		$this->testBasePath = rtrim(getenv('YAPEPBASE_TEST_TEMPPATH'), DIRECTORY_SEPARATOR);

		if (empty($this->testBasePath)) {
			$this->markTestSkipped('Test cannot be done without a test directory');
		} else {
			$this->testBasePath .= DIRECTORY_SEPARATOR;
		}

		if (stripos(PHP_OS, 'WIN') === 0) {
			$this->markTestSkipped('The Unix file handler tests can not be run on Windows');
		}

		if (!file_exists($this->testBasePath) && !mkdir($this->testBasePath, 0755, true)) {
			$this->markTestSkipped('The given path does not exist, and cannot be created: ' . $this->testBasePath);
		}

		$this->cleanupTestDirectory();

		$this->fileHandler = new FileHandlerUnix();
	}

	/**
	 * Tears down the fixture, for example, close a network connection.
	 * This method is called after a test is executed.
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
		foreach ($this->filesToRemove as $file) {
			if (file_exists($this->testBasePath . $file)) {
				unlink($this->testBasePath . $file);
			}
		}

		foreach ($this->directoriesToRemove as $directory) {
			if (file_exists($this->testBasePath . $directory)) {
				rmdir($this->testBasePath . $directory);
			}
		}
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

		$file = 'test.txt';
		$fullPath = $this->testBasePath . $file;

		touch($fullPath);

		$this->assertTrue(file_exists($fullPath), 'Precondition failed. Unable to create test file: ' . $fullPath);
		$this->assertEquals(0, fileowner($fullPath), 'Precondition failed. Invalid owner for new file.');
		clearstatcache();
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
