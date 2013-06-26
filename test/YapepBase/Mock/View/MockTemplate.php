<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBase
 * @subpackage Mock\View
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Mock\View;


use YapepBase\Mock\View\MockLayout;

/**
 * Mock class for a template.
 *
 * @codeCoverageIgnore
 *
 * @package    YapepBase
 * @subpackage Mock\View
 */
class MockTemplate extends \YapepBase\View\TemplateAbstract {

	protected $required = array('var1');
	protected $var1;
	protected $var2;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->setLayout(new MockLayout());
	}

	/**
	 * Does the actual rendering.
	 *
	 * @return void
	 */
	protected function renderContent() {
		echo 'test output';
	}

	/**
	 * Displays the given block
	 *
	 * @param \YapepBase\View\BlockAbstract $block   The block.
	 *
	 * @return void
	 */
	public function renderBlock(\YapepBase\View\BlockAbstract $block) {
		parent::renderBlock($block);
	}
}
