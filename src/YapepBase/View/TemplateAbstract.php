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

use YapepBase\View\ViewAbstract;
use YapepBase\View\LayoutAbstract;

/**
 * Template class. Represents the content of the page.
 *
 * @package    YapepBase
 * @subpackage View
 */
abstract class TemplateAbstract extends ViewAbstract {
	/**
	 * Holds the layout.
	 *
	 * @var \YapepBase\View\LayoutAbstract
	 */
	protected $layout = null;

	/**
	 * Sets the layout.
	 *
	 * @param \YapepBase\View\LayoutAbstract $layout   The layout object.
	 *
	 * @return void
	 */
	public function setLayout(LayoutAbstract $layout) {
		$this->layout = $layout;
	}

	/**
	 * Returns the current layout
	 *
	 * @return \YapepBase\View\LayoutAbstract
	 */
	public function getLayout() {
		return $this->layout;
	}

	/**
	 * Renders the view and prints it.
	 *
	 * @return void
	 */
	public function render() {
		if ($this->layout !== null) {
			ob_start();
			parent::render();
			$content = ob_get_clean();
			$this->layout->setInnerContent($content);
			$this->layout->render();
		}
		else {
			parent::render();
		}
	}
}
