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

use YapepBase\File\ResourceHandlerPhp;

/**
 * Test class for ResourceHandlerPhp.
 *
 * @package    YapepBase
 * @subpackage File
 */
class ResourceHandlerPhpTest extends  \PHPUnit_Framework_TestCase {

	/**
	 * Path to the test file.
	 *
	 * @var string
	 */
	protected $filePath;

	protected $fileContent;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		parent::setUp();

		$directoryName = 'test';
		$filename = 'test.txt';
		$this->fileContent = 'test' . PHP_EOL . 'test'. PHP_EOL . 'test'. PHP_EOL . 'test';

		vfsStreamWrapper::register();
		$directory = new vfsStreamDirectory($directoryName);
		$file = new vfsStreamFile($filename);
		$file->setContent($this->fileContent);
		$directory->addChild($file);
		vfsStreamWrapper::setRoot($directory);

		$this->filePath = vfsStream::url($directoryName . DIRECTORY_SEPARATOR . $filename);
	}

	/**
	 * Tests the __construct() method.
	 *
	 * @return void.
	 */
	public function testConstruct() {
		// Use valid parameters
		try {
			new ResourceHandlerPhp($this->filePath, 0);
		}
		catch (\Exception $e) {
			$this->fail('The constructor should now throw an exception when its called in the right way');
		}

		// Use invalid access type
		try {
			$invalidAccessType = ResourceHandlerPhp::ACCESS_TYPE_WRITE
				| ResourceHandlerPhp::ACCESS_TYPE_POINTER_AT_THE_END
				| ResourceHandlerPhp::ACCESS_TYPE_TRUNCATE
				| 128;
			new ResourceHandlerPhp($this->filePath, $invalidAccessType);
			$this->fail('The constructor should throw an exception when its called with an invalid access type');
		}
		catch (\Exception $e) {
			$this->assertInstanceOf('\YapepBase\Exception\ParameterException', $e);
		}

		// Try to open a nonexistent file
		// Use invalid access type
		try {
			$nonexistentFilePath = 'nonExistent/non.exist';
			new ResourceHandlerPhp($nonexistentFilePath, 0);
			$this->fail('The constructor should throw an exception when its called with a nonExistent path');
		}
		catch (\Exception $e) {
			$this->assertInstanceOf('\RuntimeException', $e);
		}
	}

	/**
	 * Tests the getCharacter() method.
	 *
	 * @return void
	 */
	public function testGetCharacter() {
		$resourceHandler = new ResourceHandlerPhp($this->filePath, 0);

		for ($i = 0; $i < strlen($this->fileContent); $i++) {
			$this->assertEquals($this->fileContent[$i], $resourceHandler->getCharacter());
		}
		$this->assertFalse($resourceHandler->getCharacter());
	}

	/**
	 * Tests the getLine() method.
	 *
	 * @return void
	 */
	public function testGetLine() {
		$resourceHandler = new ResourceHandlerPhp($this->filePath, 0);
		$lines = explode(PHP_EOL, $this->fileContent);

		for ($i = 0; $i < substr_count($this->fileContent, PHP_EOL); $i++) {
			$this->assertEquals($lines[$i] . PHP_EOL, $resourceHandler->getLine());
		}
		$this->assertEquals($lines[count($lines) - 1], $resourceHandler->getLine());

		$this->assertFalse($resourceHandler->getLine());
	}

	/**
	 * Tests the checkIfPointerIsAtTheEnd() method.
	 *
	 * @return void
	 */
	public function testCheckIfPointerIsAtTheEnd() {
		$resourceHandler = new ResourceHandlerPhp($this->filePath, 0);

		for ($i = 0; $i < strlen($this->fileContent); $i++) {
			$this->assertFalse($resourceHandler->checkIfPointerIsAtTheEnd());
			$this->assertEquals($this->fileContent[$i], $resourceHandler->getCharacter());
		}
		$this->assertTrue($resourceHandler->checkIfPointerIsAtTheEnd());
	}
}