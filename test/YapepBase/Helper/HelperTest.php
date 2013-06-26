<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Helper
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Helper;

use YapepBase\Application;
use YapepBase\DependencyInjection\SystemContainer;
use YapepBase\Mock\Helper\HelperMock;

/**
 * Test class for helpers
 *
 * @package    YapepBase
 * @subpackage Helper
 */
class HelperTest extends \YapepBase\BaseTest {

	protected function setUp() {
		parent::setUp();
		Application::getInstance()->setI18nTranslator(new \YapepBase\Mock\I18n\TranslatorMock(
			function($sourceClass, $string, $params, $language) {
				return json_encode(array(
					'class'    => $sourceClass,
					'string'   => $string,
					'params'   => $params,
					'language' => $language,
				));
			}
		));
	}

	protected function tearDown() {
		parent::tearDown();
		Application::getInstance()->clearI18nTranslator();
	}

	public function testTranslation() {
		$helper = new HelperMock();
		$expectedResult = array(
			'class' => 'YapepBase\Mock\Helper\HelperMock',
			'string' => 'test',
			'params' => array('testParam' => 'testValue'),
			'language' => 'en',
		);
		$this->assertSame($expectedResult, json_decode($helper->_('test', array('testParam' => 'testValue'), 'en'),
			true), 'The translator method does not return the expected result');
	}

}