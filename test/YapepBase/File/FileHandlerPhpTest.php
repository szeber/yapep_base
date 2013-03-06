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


use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamFile;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamWrapper;

use YapepBase\File\FileHandlerPhp;

/**
 * Test class for FileHandlerPhp.
 *
 * @package    YapepBase
 * @subpackage File
 */
class FileHandlerPhpTest extends  \PHPUnit_Framework_TestCase {

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

		$this->fileHandler = new FileHandlerPhp();
		vfsStreamWrapper::register();

		try {
			$testPath = $this->getTestPath();

			// Remove the files
			foreach ($this->fileHandler as $path) {
				$fullPath = $testPath . $path;
				if (is_file($fullPath)) {
					unlink($fullPath);
				}
			}
			// Remove the directories
			foreach ($this->fileHandler as $path) {
				$fullPath = $testPath . $path;
				if (is_dir($fullPath)) {
					rmdir($fullPath);
				}
			}
		}
		catch (\PHPUnit_Framework_SkippedTestError $e) {
		}
		catch (\PHPUnit_Framework_AssertionFailedError $e) {
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
	 * Tests the touch() method.
	 *
	 * @return void
	 */
	public function testTouch() {
		$testPath = $this->getTestPath();
		$testFile = $testPath . 'test.txt';

		// Create a file
		$timeModification = time() - 900;
		$timeAccess = time() - 800;
		$this->fileHandler->touch($testFile, $timeModification, $timeAccess);

		$this->assertEquals($timeModification, filemtime($testFile));
		$this->assertEquals($timeAccess, fileatime($testFile));
	}

	/**
	 * Tests the makeDirectory() method.
	 *
	 * @return void
	 */
	public function testMakeDirectory() {
		$directoryName = 'test';
		$directory = new vfsStreamDirectory($directoryName);
		vfsStreamWrapper::setRoot($directory);

		$path = vfsStream::url($directoryName)
			. DIRECTORY_SEPARATOR . 'test'
			. DIRECTORY_SEPARATOR . 'test';

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
		$directoryName = 'test';
		$directory = new vfsStreamDirectory($directoryName);
		vfsStreamWrapper::setRoot($directory);

		$fileContent = 'testestest';
		$filePath = vfsStream::url($directoryName) . DIRECTORY_SEPARATOR . 'test.txt';

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

		$this->markTestIncomplete('Find a way to test the lock');
	}

	/**
	 * Tests the changeOwner() method.
	 *
	 * @return void
	 */
	public function testChangeOwner() {
		$this->markTestIncomplete();
	}

	/**
	 * Tests the changeMode() method.
	 *
	 * @return void
	 */
	public function testChangeMode() {
		$filePath = $this->getTestPath() . 'test.txt';
		touch($filePath);

		$this->fileHandler->changeMode($filePath, 0777);

		$this->assertEquals('0777', substr(sprintf('%o', fileperms($filePath)), -4));
	}

	/**
	 * Tests the copy() method.
	 *
	 * @return void
	 */
	public function testCopy() {
		$directoryName = 'test';
		$directory = new vfsStreamDirectory($directoryName);
		$filename = 'test.txt';
		$directory->addChild(new vfsStreamFile($filename));
		vfsStreamWrapper::setRoot($directory);

		$filePath = vfsStream::url($directoryName . DIRECTORY_SEPARATOR . $filename);

		file_put_contents($filePath, 'test');
		$destinationPath = $this->getTestPath() . 'test.txt';

		$this->fileHandler->copy($filePath, $destinationPath);

		$this->assertEquals(file_get_contents($filePath), file_get_contents($destinationPath));
	}

	/**
	 * Tests the remove() method.
	 *
	 * @return void
	 */
	public function testRemove() {
		$directoryName = 'test';
		$directory = new vfsStreamDirectory($directoryName);
		$filename = 'test.txt';
		$directory->addChild(new vfsStreamFile($filename));
		vfsStreamWrapper::setRoot($directory);

		$directoryPath = vfsStream::url($directoryName);
		$filePath = vfsStream::url($directoryName . DIRECTORY_SEPARATOR . $filename);

		$this->fileHandler->remove($filePath);
		$this->assertFalse(file_exists($filePath));

		try {
			$this->fileHandler->remove($directoryPath);

			$this->fail('The called method should throw an Exception if a directory has been given');
		}
		catch (\YapepBase\Exception\File\Exception $e) {
		}
	}


	/**
	 * Tests the removeDirectory() method.
	 *
	 * @return void
	 */
	public function testRemoveDirectory() {
		$directoryName = 'test';
		$directory = new vfsStreamDirectory($directoryName);
		$filename = 'test.txt';
		$directory->addChild(new vfsStreamFile($filename));
		vfsStreamWrapper::setRoot($directory);

		$directoryPath = vfsStream::url($directoryName);
		$filePath = vfsStream::url($directoryName . DIRECTORY_SEPARATOR . $filename);

		try {
			$this->fileHandler->removeDirectory($filePath);

			$this->fail('The called method should throw an Exception if a file has been given');
		}
		catch (\YapepBase\Exception\File\Exception $e) {
		}

		try {
			$this->fileHandler->removeDirectory($directoryPath);

			$this->fail('The called method should throw an Exception if the directory is not empty,'
				. ' and recursive mode is off');
		}
		catch (\YapepBase\Exception\File\Exception $e) {
		}

		$this->fileHandler->removeDirectory($directoryPath, true);

		$this->assertFalse(file_exists($filePath));
		$this->assertFalse(file_exists($directoryPath));
	}

	/**
	 * Tests the move() method.
	 *
	 * @return void
	 */
	public function testMove() {
		$directoryName = 'test';
		$directory = new vfsStreamDirectory($directoryName);
		$filename = 'test.txt';
		$directory->addChild(new vfsStreamFile($filename));
		vfsStreamWrapper::setRoot($directory);

		$filePath = vfsStream::url($directoryName . DIRECTORY_SEPARATOR . $filename);
		$destinationPath = vfsStream::url($directoryName . DIRECTORY_SEPARATOR . 'test1.txt');

		$fileContent = 'test';
		file_put_contents($filePath, $fileContent);

		$this->fileHandler->move($filePath, $destinationPath);

		$this->assertEquals($fileContent, file_get_contents($destinationPath));
		$this->assertFalse(file_exists($filePath));
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
		$directoryName = 'test';
		$directory = new vfsStreamDirectory($directoryName);
		$filename = 'test.txt';
		$directory->addChild(new vfsStreamFile($filename));
		vfsStreamWrapper::setRoot($directory);

		$filePath = vfsStream::url($directoryName . DIRECTORY_SEPARATOR . $filename);

		$this->assertFalse($this->fileHandler->checkIsPathExists($filePath . 1));
		$this->assertTrue($this->fileHandler->checkIsPathExists($filePath));
	}

	/**
	 * Tests the getAsString() method.
	 *
	 * @return void
	 */
	public function testGetAsString() {
		$directoryName = 'test';
		$directory = new vfsStreamDirectory($directoryName);
		$filename = 'test.txt';
		$directory->addChild(new vfsStreamFile($filename));
		vfsStreamWrapper::setRoot($directory);

		$filePath = vfsStream::url($directoryName . DIRECTORY_SEPARATOR . $filename);
		$fileContent = 'test';
		file_put_contents($filePath, $fileContent);

		$this->assertEquals($fileContent, $this->fileHandler->getAsString($filePath));

		for ($i = 0; $i < strlen($fileContent); $i++) {
			$this->assertEquals($fileContent[$i], $this->fileHandler->getAsString($filePath, $i, 1));
		}
	}

	/**
	 * Tests the getList() method.
	 *
	 * @return void
	 */
	public function testGetList() {
		$directoryName = 'test';
		$directory = new vfsStreamDirectory($directoryName);
		$filename = 'test.txt';
		$filename2 = 'test1.txt';
		$directory->addChild(new vfsStreamFile($filename));
		$directory->addChild(new vfsStreamFile($filename2));
		vfsStreamWrapper::setRoot($directory);

		$directoryPath = vfsStream::url($directoryName);

		$list = array($filename, $filename2);
		sort($list);
		$queriedList = $this->fileHandler->getList($directoryPath);
		sort($queriedList);

		$this->assertEquals($list, $queriedList);

		try {
			$this->fileHandler->getList($directoryPath . DIRECTORY_SEPARATOR . $filename);
			$this->fail('This method should throw an exception if a not-directory has been given');
		}
		catch (\YapepBase\Exception\File\Exception $e) {
		}
	}

	/**
	 * Tests the getListByGlob() method.
	 *
	 * @return void
	 */
	public function testGetListByGlob() {
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
		catch (\YapepBase\Exception\File\Exception $e) {
		}
	}

	/**
	 * Tests the getModificationTime() method.
	 *
	 * @return void
	 */
	public function testGetModificationTime() {
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
		catch (\YapepBase\Exception\File\Exception $e) {
		}
	}

	/**
	 * Tests the getSize() method.
	 *
	 * @return void
	 */
	public function testGetSize() {
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
		catch (\YapepBase\Exception\File\Exception $e) {
		}
	}

	/**
	 * Tests the checkIsDirectory() method.
	 *
	 * @return void
	 */
	public function testCheckIsDirectory() {
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