<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Test\Batch
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Batch;

use YapepBase\Batch\CliUserInterfaceHelper;
use YapepBase\Exception\Exception;

/**
 * Test for the CliUserInterfaceHelper class
 *
 * @package    YapepBase
 * @subpackage Test\Batch
 *
 * @todo   Implement testGetParsedArgs().
 */
class CliUserInterfaceHelperTest extends \YapepBase\BaseTest {

	public function testOutput() {
		$helper = new CliUserInterfaceHelper('test script', 'test.php');

		$testUsage = $helper->addUsage('Test');
		$test2Usage = $helper->addUsage('Test2');

		$loremIpsum = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam imperdiet, erat sit amet
		porttitor volutpat, enim massa vestibulum purus, nec imperdiet tortor ligula nec augue. Donec consectetur diam
		sed lacus bibendum dictum. Aenean luctus, est eget vestibulum aliquam, mauris nisi vehicula orci, eget aliquam
		tellus lorem in nisl. Curabitur felis est, imperdiet a molestie in, aliquet eget metus. Cras pretium accumsan
		augue vel euismod.';

		$helper->addSwitch('a', 'test', $loremIpsum, $testUsage, true, 'test', true);
		$helper->addSwitch('b', null, 'Test 2', null);
		$helper->addSwitch(null, 'test3', 'Test 3', $test2Usage, false);
		$helper->addSwitch('d', 'test4', 'Test 4', array($testUsage, $test2Usage), true, 'test4');

		$expectedOutput = 'Usages:

    Test:
        test.php  -b  [-a[=<test>]|--test[=<test>]]
        [-d=<test4>|--test4=<test4>]

    Test2:
        test.php  -b  --test3  [-d=<test4>|--test4=<test4>]



test script

  -a, --test    Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                Etiam imperdiet, erat sit amet porttitor volutpat, enim
                massa vestibulum purus, nec imperdiet tortor ligula nec
                augue. Donec consectetur diam sed lacus bibendum dictum.
                Aenean luctus, est eget vestibulum aliquam, mauris nisi
                vehicula orci, eget aliquam tellus lorem in nisl. Curabitur
                felis est, imperdiet a molestie in, aliquet eget metus.
                Cras pretium accumsan augue vel euismod.
  -b            Test 2
      --test3   Test 3
  -d, --test4   Test 4


';

		$this->assertSame($expectedOutput, $helper->getUsageOutput(true));

		$helper->setErrorMessage('Test error');

		$expectedOutput = 'Error:
    Test error


Usages:

    Test:
        test.php  -b  [-a[=<test>]|--test[=<test>]]
        [-d=<test4>|--test4=<test4>]

    Test2:
        test.php  -b  --test3  [-d=<test4>|--test4=<test4>]

';
		$this->assertSame($expectedOutput, $helper->getUsageOutput(false));
	}

	public function testErrors() {
		$helper = new CliUserInterfaceHelper('test script', 'test.php');

		try {
			$helper->getUsageOutput();
			$this->fail('No Exception is thrown when getting usage without any defined usages');
		} catch (Exception $e) {
		}

		$testUsage = $helper->addUsage('test');
		$helper->addSwitch('a', 'test', 'Test', null);

		try {
			$helper->addSwitch(null, null, 'Test', null);
			$this->fail('No Exception is thrown when adding a switch with neither short nor long name');
		} catch (Exception $e) {
		}

		try {
			$helper->addSwitch('a', null, 'Test', null);
			$this->fail('No exception is thrown when adding a duplicate short switch');
		} catch (Exception $e) {
		}

		try {
			$helper->addSwitch(null, 'test', 'Test', null);
			$this->fail('No exception is thrown when adding a duplicate long switch');
		} catch (Exception $e) {
		}

		try {
			$helper->addSwitch('b', null, 'test', $testUsage+1);
			$this->fail('No exception is thrown when adding a switch to an invalid usage');
		} catch (Exception $e) {
		}

		try {
			$helper->addSwitch('c', null, 'test', array($testUsage, $testUsage+1));
			$this->fail('No exception is thrown when adding a switch to an invalid usage with an array');
		} catch (Exception $e) {
		}
	}
}