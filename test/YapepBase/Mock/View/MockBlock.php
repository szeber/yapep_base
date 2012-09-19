<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Mock\View
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Mock\View;


/**
 * BlockAbstract class, should be extended by every Block.
 *
 * @package    YapepBase
 * @subpackage Mock\View
 */
class MockBlock extends \YapepBase\View\BlockAbstract {

	/**
	 * The content to render.
	 *
	 * @var string
	 */
	protected $content;

	/**
	 * Sets the content to render.
	 *
	 * @param string $content   The content.
	 *
	 * @return void
	 */
	public function setContent($content) {
		$this->content = $content;
	}

	/**
	 * Returns the content.
	 *
	 * @return string
	 */
	public function getContent() {
		return $this->content;
	}

	/**
	 * Does the actual rendering.
	 *
	 * @return void
	 */
	public function renderContent() {
		echo $this->content;
	}

}
