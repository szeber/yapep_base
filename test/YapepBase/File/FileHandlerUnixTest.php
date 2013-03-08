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

use YapepBase\Exception\File\Exception;
use YapepBase\File\FileHandlerUnix;

/**
 * Test class for FileHandlerUnix.
 *
 * @package    YapepBase
 * @subpackage File
 */
class FileHandlerUnixTest extends  \PHPUnit_Framework_TestCase {

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
	 * Add here all the FS resources you are creating in a test.
	 *
	 * @var array
	 */
	protected $filesToRemove = array(
		'test.txt',
		'test1.txt',
		'test.php',
		'testDir/testFile',
	);

	protected $directoriesToRemove = array(
		'testDir',
	);

	protected $testBasePath;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp() {
		parent::setUp();

		$this->testBasePath = rtrim(getenv('YAPEPBASE_TEST_TEMPPATH'), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

		if (empty($this->testBasePath)) {
			$this->markTestSkipped('Test cannot be done without a test directory');
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
	 * Tests the touch() method.
	 *
	 * @return void
	 */
	public function testTouch() {
		$testFile = $this->testBasePath . 'test.txt';

		$this->assertFalse(file_exists($testFile), 'Precondition failed: ' . $testFile . ' exists before creation');

		// Create a file
		$timeModification = time() - 900;
		$timeAccess = time() - 800;
		$this->fileHandler->touch($testFile, $timeModification, $timeAccess);

		$this->assertTrue(file_exists($testFile), 'File does not exist after touch operation');
		$this->assertEquals($timeModification, filemtime($testFile), 'Invalid modification time');
		$this->assertEquals($timeAccess, fileatime($testFile), 'Invalid access time');
	}

	/**
	 * Tests the makeDirectory() method.
	 *
	 * @return void
	 */
	public function testMakeDirectory() {
		$directoryName = 'testDir';
		$path = $this->testBasePath . $directoryName;

		$mode = 0777;
		$this->fileHandler->makeDirectory($path, 0777, true);

		$this->assertTrue(file_exists($path));
		$this->assertEquals(decoct($mode), substr(sprintf('%o', fileperms($path)), -3));
	}

	/**
	 * Tests the write() method.
	 *
	 * @return void
	 */
	public function testWrite() {
		$fileContent = 'testestest';
		$filePath = $this->testBasePath . 'test.txt';

		$this->fileHandler->write($filePath, $fileContent);

		$this->assertEquals($fileContent, file_get_contents($filePath));

		// Try to rewrite the content of the file
		$fileContent = 'fooobaaar';
		$this->fileHandler->write($filePath, $fileContent);

		$this->assertEquals($fileContent, file_get_contents($filePath));

		// Try to append new content to the file
		$newContent = PHP_EOL . 'baaaz';
		$this->fileHandler->write($filePath, $newContent, true);

		$this->assertEquals($fileContent . $newContent, file_get_contents($filePath));
	}

	/**
	 * Tests the changeOwner() method.
	 *
	 * @return void
	 */
	public function testChangeOwner() {
		$this->markTestIncomplete(
			'This can only be tested with root, but it would be unwise to run the tests as root');
	}

	/**
	 * Tests the changeMode() method.
	 *
	 * @return void
	 */
	public function testChangeMode() {
		$filePath = $this->testBasePath . 'test.txt';
		$this->assertFalse(file_exists($filePath), 'Precondition failed: ' . $filePath . ' already exists');
		touch($filePath);
		$this->assertTrue(file_exists($filePath), 'Precondition failed: failed to create ' . $filePath . ' with touch');
		chmod($filePath, 0700);
		clearstatcache();
		$this->assertNotEquals('0777', substr(sprintf('%o', fileperms($filePath)), -4),
			'Precondition failed: The created test file has permission 0777 after creation');

		$this->fileHandler->changeMode($filePath, 0777);
		clearstatcache();
		$this->assertEquals('0777', substr(sprintf('%o', fileperms($filePath)), -4),
		'Invalid permission after setting file mode');
	}

	/**
	 * Tests the copy() method.
	 *
	 * @return void
	 */
	public function testCopy() {
		$sourcePath = $this->testBasePath . 'test.txt';
		$destinationPath = $this->testBasePath . 'test1.txt';
		$fileContents = 'foobar';

		file_put_contents($sourcePath, $fileContents);

		$this->assertFalse(file_exists($destinationPath),
			'Precondition failed. Destination file exists ' . $destinationPath);

		$this->fileHandler->copy($sourcePath, $destinationPath);

		$this->assertTrue(file_exists($destinationPath), 'The destination file was not created by the copy');
		$this->assertTrue(file_exists($sourcePath), 'The source file does not exist after the copy');

		$this->assertEquals($fileContents, file_get_contents($destinationPath), 'The copied file has invalid contents');
	}

	/**
	 * Tests the remove() method.
	 *
	 * @return void
	 */
	public function testRemove() {
		$filePath = $this->testBasePath . 'test.txt';
		$directoryPath = $this->testBasePath . 'testDir';

		file_put_contents($filePath, 'test');
		mkdir($directoryPath);

		$this->assertTrue(is_file($filePath),
			'Precondition failed. The test file was not created: ' . $filePath);
		$this->assertTrue(is_dir($directoryPath),
			'Precondition failed. The test directory was not created: ' . $directoryPath);

		$this->fileHandler->remove($filePath);

		$this->assertFalse(file_exists($filePath), 'The file was not deleted');

		try {
			$this->fileHandler->remove($directoryPath);

			$this->fail('The called method should throw an Exception if a directory has been given');
		}
		catch (Exception $e) {
			$this->assertTrue(file_exists($directoryPath),
				'An exception was thrown stating the directory is not a regular file, but the directory was removed: '
					. $directoryPath);
		}
	}


	/**
	 * Tests the removeDirectory() method.
	 *
	 * @return void
	 */
	public function testRemoveDirectory() {
		$filePath = $this->testBasePath . 'testDir' . DIRECTORY_SEPARATOR . 'testFile';
		$directoryPath = $this->testBasePath . 'testDir';

		mkdir($directoryPath);
		file_put_contents($filePath, 'test');

		$this->assertTrue(is_file($filePath),
			'Precondition failed. The test file was not created: ' . $filePath);
		$this->assertTrue(is_dir($directoryPath),
			'Precondition failed. The test directory was not created: ' . $directoryPath);

		try {
			$this->fileHandler->removeDirectory($filePath);

			$this->fail('The called method should throw an Exception if a file has been given');
		}
		catch (Exception $e) {
			$this->assertTrue(file_exists($directoryPath),
				'An exception was thrown stating the file is not a directory, but the file was removed: '
					. $filePath);
		}

		try {
			$this->fileHandler->removeDirectory($directoryPath);

			$this->fail('The called method should throw an Exception if the directory is not empty,'
				. ' and recursive mode is off');
		}
		catch (Exception $e) {
			$this->assertTrue(file_exists($directoryPath),
				'An exception was thrown stating the directory is not a empty, but the directory was removed: '
					. $directoryPath);
		}

		$this->fileHandler->removeDirectory($directoryPath, true);

		$this->assertFalse(file_exists($filePath), 'The file was not removed');
		$this->assertFalse(file_exists($directoryPath), 'The directory was not removed');
	}

	/**
	 * Tests the move() method.
	 *
	 * @return void
	 */
	public function testMove() {
		$sourcePath = $this->testBasePath . 'test.txt';
		$destinationPath = $this->testBasePath . 'test1.txt';
		$fileContents = 'foobar';

		file_put_contents($sourcePath, $fileContents);

		$this->assertFalse(file_exists($destinationPath),
			'Precondition failed. Destination file exists ' . $destinationPath);

		$this->fileHandler->move($sourcePath, $destinationPath);

		$this->assertTrue(file_exists($destinationPath), 'The destination file was not created by the move');
		$this->assertFalse(file_exists($sourcePath), 'The source file exists after the move');

		$this->assertEquals($fileContents, file_get_contents($destinationPath), 'The moved file has invalid contents');
	}

	/**
	 * Tests the getParentDirectory() method.
	 *
	 * @return void
	 */
	public function testGetParentDirectory() {
		$testDirectory = '/test/test1/test2';
		$parentDirectory = '/test/test1';

		$this->assertEquals($parentDirectory, $this->fileHandler->getParentDirectory($testDirectory));
	}

	/**
	 * Tests the getCurrentDirectory() method.
	 *
	 * @return void
	 */
	public function testGetCurrentDirectory() {
		$this->assertEquals(getcwd(), $this->fileHandler->getCurrentDirectory());
	}

	/**
	 * Tests the checkIsPathExists() method.
	 *
	 * @return void
	 */
	public function testCheckIsPathExists() {
		$filePath = $this->testBasePath . 'test.txt';

		touch($filePath);

		$this->assertTrue(file_exists($filePath), 'Precondition failed. File was not created: ' . $filePath);
		$this->assertFalse(file_exists($filePath . 1), 'Precondition failed. File exists: ' . $filePath . 1);

		$this->assertFalse($this->fileHandler->checkIsPathExists($filePath . 1),
			'TRUE returned for checking if a non-existing file exists');
		$this->assertTrue($this->fileHandler->checkIsPathExists($filePath),
			'FALSE returned for checking if an existing file exists');
	}

	/**
	 * Tests the getAsString() method.
	 *
	 * @return void
	 */
	public function testGetAsString() {
		$filePath = $this->testBasePath . 'test.txt';

		$fileContent = 'test';
		file_put_contents($filePath, $fileContent);

		$this->fileHandler->getAsString($filePath);

		$this->assertEquals($fileContent, $this->fileHandler->getAsString($filePath),
			'Invalid file contents when reading full file');

		for ($i = 0; $i < strlen($fileContent); $i++) {
			$this->assertEquals($fileContent[$i], $this->fileHandler->getAsString($filePath, $i, 1),
				'Invalid file contents read when reading byte ' . $i . ' from the file');
		}
	}

	/**
	 * Tests the getList() method.
	 *
	 * @return void
	 */
	public function testGetList() {
		$filename = 'test.txt';
		$filename2 = 'test1.txt';

		touch($this->testBasePath . $filename);
		touch($this->testBasePath . $filename2);

		$list = array($filename, $filename2);
		sort($list);
		$queriedList = $this->fileHandler->getList($this->testBasePath);

		$this->assertEquals($list, $queriedList);

		try {
			$this->fileHandler->getList($this->testBasePath . DIRECTORY_SEPARATOR . $filename);
			$this->fail('This method should throw an exception if a not-directory has been given');
		}
		catch (Exception $e) {
		}
	}

	/**
	 * Tests the getListByGlob() method.
	 *
	 * @return void
	 */
	public function testGetListByGlob() {
		$this->markTestIncomplete('Not created yet');
		$directoryPath = $this->getTestPath();
		$filename = 'test.txt';
		$filename2 = 'test1.txt';
		$filename3 = 'test.php';

		touch($directoryPath . $filename);
		touch($directoryPath . $filename2);
		touch($directoryPath . $filename3);

		$list = array($filename, $filename2);
		sort($list);
		$queriedList = $this->fileHandler->getListByGlob($directoryPath, '*.txt');
		sort($queriedList);

		$this->assertEquals($list, $queriedList);

		try {
			$this->fileHandler->getList($directoryPath . DIRECTORY_SEPARATOR . $filename);
			$this->fail('This method should throw an exception if a not-directory has been given');
		}
		catch (Exception $e) {
		}
	}

	/**
	 * Tests the getModificationTime() method.
	 *
	 * @return void
	 */
	public function testGetModificationTime() {
		$this->markTestIncomplete('Not created yet');
		$directoryName = 'test';
		$directory = new vfsStreamDirectory($directoryName);
		$filename = 'test.txt';
		$directory->addChild(new vfsStreamFile($filename));
		vfsStreamWrapper::setRoot($directory);

		$filePath = vfsStream::url($directoryName . DIRECTORY_SEPARATOR . $filename);

		$this->assertEquals(filemtime($filePath), $this->fileHandler->getModificationTime($filePath));

		try {
			$this->fileHandler->getModificationTime($filePath . DIRECTORY_SEPARATOR . 'nonexistent.txt');
			$this->fail('This method should throw an exception if a nonexistent path given');
		}
		catch (Exception $e) {
		}
	}

	/**
	 * Tests the getSize() method.
	 *
	 * @return void
	 */
	public function testGetSize() {
		$this->markTestIncomplete('Not created yet');
		$directoryName = 'test';
		$directory = new vfsStreamDirectory($directoryName);
		$filename = 'test.txt';
		$directory->addChild(new vfsStreamFile($filename));
		vfsStreamWrapper::setRoot($directory);

		$filePath = vfsStream::url($directoryName . DIRECTORY_SEPARATOR . $filename);

		$this->assertEquals(filesize($filePath), $this->fileHandler->getSize($filePath));

		try {
			$this->fileHandler->getSize($filePath . DIRECTORY_SEPARATOR . 'nonexistent.txt');
			$this->fail('This method should throw an exception if a nonexistent path given');
		}
		catch (Exception $e) {
		}
	}

	/**
	 * Tests the checkIsDirectory() method.
	 *
	 * @return void
	 */
	public function testCheckIsDirectory() {
		$this->markTestIncomplete('Not created yet');
		$directoryName = 'test';
		$directory = new vfsStreamDirectory($directoryName);
		$filename = 'test.txt';
		$directory->addChild(new vfsStreamFile($filename));
		vfsStreamWrapper::setRoot($directory);

		$filePath = vfsStream::url($directoryName . DIRECTORY_SEPARATOR . $filename);
		$directoryPath = vfsStream::url($directoryName);

		$this->assertFalse($this->fileHandler->checkIsDirectory($filePath));
		try {
			$this->fileHandler->checkIsDirectory($directoryPath . DIRECTORY_SEPARATOR . 'nonexistent');
			$this->fail('No exception is thrown for a missing directory');
		} catch (\YapepBase\Exception\File\NotFoundException $e) {
			$this->assertEquals($directoryPath . DIRECTORY_SEPARATOR . 'nonexistent', $e->getFilename(),
				'The NotFoundException contains an invalid filename');
			$this->assertContains('does not exist', $e->getMessage());
		}
		$this->assertTrue($this->fileHandler->checkIsDirectory($directoryPath));
	}

	/**
	 * Tests the checkIsFile() method.
	 *
	 * @return void
	 */
	public function testCheckIsFile() {
		$this->markTestIncomplete('Not created yet');
		$directoryName = 'test';
		$directory = new vfsStreamDirectory($directoryName);
		$filename = 'test.txt';
		$directory->addChild(new vfsStreamFile($filename));
		vfsStreamWrapper::setRoot($directory);

		$filePath = vfsStream::url($directoryName . DIRECTORY_SEPARATOR . $filename);
		$directoryPath = vfsStream::url($directoryName);

		$this->assertTrue($this->fileHandler->checkIsFile($filePath));
		try {
			$this->fileHandler->checkIsFile($directoryPath . DIRECTORY_SEPARATOR . 'nonexistent');
			$this->fail('No exception is thrown for a missing file');
		} catch (\YapepBase\Exception\File\NotFoundException $e) {
			$this->assertEquals($directoryPath . DIRECTORY_SEPARATOR . 'nonexistent', $e->getFilename(),
				'The NotFoundException contains an invalid filename');
			$this->assertContains('does not exist', $e->getMessage());
		}
		$this->assertFalse($this->fileHandler->checkIsFile($directoryPath));
	}

	/**
	 * Tests the getBaseName() method.
	 *
	 * @return void
	 */
	public function testGetBaseName() {
		$testPath = '/tmp/test/test.txt';

		$this->assertEquals('test.txt', $this->fileHandler->getBaseName($testPath));
		$this->assertEquals('test', $this->fileHandler->getBaseName($testPath, '.txt'));
	}
}