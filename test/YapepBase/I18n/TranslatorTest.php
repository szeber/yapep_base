<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Test\I18n
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\I18n;

use YapepBase\Mock\Storage\StorageMock;
use YapepBase\I18n\Translator;
use YapepBase\Config;
use YapepBase\Exception\ConfigException;
use YapepBase\Exception\I18n\DictionaryNotFoundException;
use YapepBase\Exception\I18n\ParameterException;
use YapepBase\Exception\I18n\TranslationNotFoundException;

/**
 * Unit test for the translator class
 *
 * @package    YapepBase
 * @subpackage Test\I18n
 */
class TranslatorTest extends \YapepBase\BaseTest {

	/**
	 * The StorageMock instance
	 *
	 * @var \YapepBase\Mock\Storage\StorageMock
	 */
	protected $storageMock;

	/**
	 * Setup method
	 *
	 * @return void
	 */
	protected function setUp() {
		parent::setUp();

		$this->storageMock = new StorageMock(false, true, array('en-Test' => array(
			'test' => 'Test text',
			'test2' => 'Test %testParam%',
		)));

		Config::getInstance()->set(array(
			'resource.i18n.translator.errorEmptyParam.paramPrefix' => '',
			'resource.i18n.translator.errorEmptyParam.paramSuffix' => '',

			'resource.i18n.translator.errorInvalidErrorMode.errorMode' => 'invalid',

			'resource.i18n.translator.strictParamCheck.strictParamChecking' =>true,

			'resource.i18n.translator.errorModeError.errorMode' => Translator::ERROR_MODE_ERROR,

			'resource.i18n.translator.errorModeNone.errorMode' => Translator::ERROR_MODE_NONE,
		));
	}

	/**
	 * Tear down method
	 *
	 * @return void
	 */
	protected function tearDown() {
		parent::tearDown();
		$this->storageMock = null;
		Config::getInstance()->clear();
	}

	/**
	 * Tests instatiations with different configurations
	 *
	 * @return void
	 */
	public function testConfiguration() {
		// Test, that it works without config
		new Translator($this->storageMock, 'en', 'default');

		try {
			new Translator($this->storageMock, 'en', 'errorEmptyParam');
			$this->fail('Empty prefix and suffix does not throw an exception');
		} catch (ConfigException $e) {
		}

		try {
			new Translator($this->storageMock, 'en', 'errorInvalidErrorMode');
			$this->fail('Invalid error mode does not throw an exception');
		} catch (ConfigException $e) {
		}
	}

	/**
	 * Tests the translation functionality
	 *
	 * @return void
	 */
	public function testTranslation() {
		$translator = new Translator($this->storageMock, 'en', 'default');
		$this->assertSame($translator->translate('Test', 'test'), 'Test text');
		$this->assertSame($translator->translate('Test', 'test2'), 'Test %testParam%');
		$this->assertSame($translator->translate('Test', 'test2', array('testParam' => 'string')), 'Test string');
	}

	/**
	 * Tests the error conditions
	 *
	 * @return void
	 */
	public function testErrors() {
		$translator = new Translator($this->storageMock, 'en', 'default');
		try {
			$translator->translate('Test2', 'test');
			$this->fail('No DictionaryNotFoundException is thrown for non-existing dictionary');
		} catch (DictionaryNotFoundException $e) {
		}
		try {
			$translator->translate('Test', 'test', array(), 'de');
			$this->fail('No DictionaryNotFoundException is thrown for non-existing dictionary');
		} catch (DictionaryNotFoundException $e) {
		}

		$translator = new Translator($this->storageMock, 'en', 'strictParamCheck');
		try {
			$translator->translate('Test', 'test', array('test' => 'test'));
			$this->fail('No ParameterException is thrown for an extra parameter with strict checking');
		} catch (ParameterException $e) {
		}
		try {
			$translator->translate('Test', 'test2');
			$this->fail('No ParameterException is thrown for a missing parameter with strict checking');
		} catch (ParameterException $e) {
		}
	}

	/**
	 * Tests missing translation error handling with different error modes
	 *
	 * @return void
	 */
	public function testErrorModes() {
		$translator = new Translator($this->storageMock, 'en', 'default');
		try {
			$translator->translate('Test', 'nonexistent');
			$this->fail('No TranslationNotFoundException is thrown for missing translation');
		} catch (TranslationNotFoundException $e) {
		}

		$translator = new Translator($this->storageMock, 'en', 'errorModeNone');
		$this->assertSame('nonexistent test2',
			$translator->translate('Test', 'nonexistent %test%', array('test' => 'test2')));
	}

}