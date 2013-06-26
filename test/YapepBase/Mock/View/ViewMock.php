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


use YapepBase\Storage\IStorage;
use YapepBase\View\BlockAbstract;
use YapepBase\View\ViewDo;

/**
 * ViewAbstract class what should be extended by every View class.
 *
 * @package    YapepBase
 * @subpackage Mock\View
 */
class ViewMock extends \YapepBase\View\ViewAbstract {

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

	/**
	 * Renders the view and prints it.
	 *
	 * @return void
	 */
	public function render() {
		parent::render();
	}

	/**
	 * Returns the set content type.
	 *
	 * @return string
	 */
	public function getContentType() {
		return $this->contentType;
	}

	/**
	 * Displays the given block
	 *
	 * @param \YapepBase\View\BlockAbstract $block   The block.
	 *
	 * @return void
	 */
	public function renderBlock(BlockAbstract $block) {
		parent::renderBlock($block);
	}

	/**
	 * Sets the view DO instance used by the view.
	 *
	 * @param \YapepBase\View\ViewDo $viewDo   The ViewDo instance to use.
	 *
	 * @return void
	 */
	public function setViewDo(ViewDo $viewDo) {
		parent::setViewDo($viewDo);
	}

	/**
	 * Returns the currently used view DO instance.
	 *
	 * @return \YapepBase\View\ViewDo
	 */
	public  function getViewDo() {
		return parent::getViewDo();
	}

	/**
	 * Sets the storage object which will be used for caching the rendered view.
	 *
	 * @param \YapepBase\Storage\IStorage $storage        The object for caching.
	 * @param array                       $keyModifiers   Associative array which holds the keys and values,
	 *                                                    what will take into consideration in the caching process.
	 * @param int                         $ttl            Time to leave in seconds.
	 *
	 * @return void
	 *
	 * @throws \YapepBase\Exception\Exception   If the storage has benn already set.
	 */
	public function setStorage(IStorage $storage, array $keyModifiers, $ttl) {
		parent::setStorage($storage, $keyModifiers, $ttl);
	}
}