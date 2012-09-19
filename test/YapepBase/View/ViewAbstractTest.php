<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   View
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\View;

use \PHPUnit_Framework_AssertionFailedError;

use YapepBase\Mime\MimeType;
use YapepBase\Mock\View\ViewMock;
use YapepBase\Mock\View\MockBlock;

/**
 * Test case for testing the ViewAbstract class
 *
 * @package    YapepBase
 * @subpackage View
 */
class ViewAbstractTest extends \PHPUnit_Framework_TestCase {

	/**
	 * The View Mock object.
	 *
	 * @var ViewMock
	 */
	protected $viewMock;

	/**
	 * Runs before each test
	 *
	 * @return void
	 */
	protected function setUp() {
		parent::setUp();

		$this->viewMock = new ViewMock();
	}

	/**
	 * Runs after each test
	 *
	 * @return void
	 */
	protected function tearDown() {
		parent::tearDown();
	}

	/**
	 * Tests the renderContent() method.
	 *
	 * @return void
	 */
	public function testRenderContent() {
		$testContent = 'Test, just for this method!' . "\n";

		$this->viewMock->setContent($testContent);

		ob_start();
		$this->viewMock->renderContent();
		$result = ob_get_clean();

		$this->assertEquals($testContent, $result, 'The rendered content should be the same as the given one');
	}

	/**
	 * Tests the render() method.
	 *
	 * @return void
	 */
	public function testRender() {
		$testContent = 'Test, just for this method!' . "\n";

		$this->viewMock->setContent($testContent);

		ob_start();
		$this->viewMock->render();
		$result = ob_get_clean();

		$this->assertEquals($testContent, $result, 'The rendered content should be the same as the given one');
	}

	/**
	 * Tests the renderBlock() method.
	 *
	 * @return void
	 */
	public function testRenderBlock() {
		$testContent = 'Test, just for this method!' . "\n";

		$block = new MockBlock();
		$block->setContent($testContent);

		ob_start();
		$this->viewMock->renderBlock($block);
		$result = ob_get_clean();

		$this->assertEquals($testContent, $result, 'The rendered content should be the same as in the block');
	}
}