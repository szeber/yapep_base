<?php

namespace YapepBase\Test;
use YapepBase\Config;

require_once dirname(__FILE__) . '/../bootstrap.php';

/**
 * Config test case.
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \YapepBase\Config
     */
    private $Config;

    /**
     * Constructs the test case.
     */
    public function __construct() {
        $this->Config = Config::getInstance();
    }

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp ()
    {
        parent::setUp();
    }
    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown ()
    {
        $this->Config->clear();
        parent::tearDown();
    }

    /**
     * Tests setting a single value, clearing and returning all items
     */
    public function testBasics() {
        $config = Config::getInstance();
        $this->assertInstanceOf('\YapepBase\Config', $config, 'Retrieved object is not a Config instance');

        $result = $this->Config->get('*');
        $this->assertInternalType('array', $result, 'Result is not an array');
        $this->assertTrue(empty($result), 'Result is not empty');

        $this->Config->set('test', 'value');

        $result = $this->Config->get('*');
        $this->assertInternalType('array', $result, 'Result is not an array');
        $this->assertFalse(empty($result), 'Result is empty after setting value');

        $this->Config->clear();

        $result = $this->Config->get('*');
        $this->assertInternalType('array', $result, 'Result is not an array');
        $this->assertTrue(empty($result), 'Result is not empty after clearing');

        $this->Config->set('test', 'value');

        $result = $this->Config->get('test');
        $this->assertSame('value', $result, 'Result not the previously set value');

        $this->Config->delete('test');

        $result = $this->Config->get('test', false);
        $this->assertFalse($result, 'Result is not empty after deleting value');

    }

    /**
     * Tests default handling
     */
    public function testDefault() {
        $this->assertNull($this->Config->get(''), 'Empty request does not return not specified default');

        $this->assertNull($this->Config->get('nonexistent'), 'Not specified default is not NULL');
        $this->assertSame('test', $this->Config->get('nonexistent', 'test'), 'Specified default does not match');
    }

    /**
     * Tests setting simple values
     */
    public function testSimpleValues () {
        $this->Config->set('test1', '123');
        $this->assertSame('123', $this->Config->get('test1'), 'Setting simple value failed');

        $this->Config->set(array('test2' => '234'));
        $this->assertSame('234', $this->Config->get('test2'), 'Setting array value failed');
        $this->assertSame('123', $this->Config->get('test1'), 'Setting array interferes with previously set values');

        $this->Config->set(array('test1' => '345'));
        $this->assertSame('345', $this->Config->get('test1'), 'overriding with array value failed');
        $this->assertSame('234', $this->Config->get('test2'),
            'Overriding with array interferes with previously set values');

        $this->Config->set('test2', '456');
        $this->assertSame('456', $this->Config->get('test2'), 'overriding with singe value failed');
        $this->assertSame('345', $this->Config->get('test1'),
            'Overriding with single value interferes with previously set values');

        $this->Config->set(array('test3' => '567', 'test4' => 678));
        $this->assertSame('567', $this->Config->get('test3'), 'Setting multiple values failed');
        $this->assertSame(678, $this->Config->get('test4'), 'Setting multiple values failed');

        $this->assertEquals(4, count($this->Config->get('*')), 'Invalid value count');
    }

    /**
     * Test default value handling
     */
    public function testDefaults() {
        $this->assertNull($this->Config->get('test'));
        $this->assertSame('123', $this->Config->get('test', '123'));
    }

    /**
     * Test configuration section handling
     */
    public function testSections () {
        $testData = array(
            'test.first' => 1,
            'test.second' => 2,
            'test.secondLevel.first' => 'first',
            'test.secondLevel.second' => 'second',
            'test2' => 'test',
        );

        $this->Config->set($testData);

        $this->assertNull($this->Config->get('test*', null), 'Returning invalid wildcard returns the default');

        $result = $this->Config->get('test.*');
        $this->assertInternalType('array', $result);
        $this->assertEquals(4, count($result));
        $this->assertArrayHasKey('first', $result);
        $this->assertSame(1, $result['first']);

        $result = $this->Config->get('test.*', null, true);
        $this->assertInternalType('array', $result);
        $this->assertEquals(4, count($result));
        $this->assertArrayHasKey('test.first', $result);
        $this->assertSame(1, $result['test.first']);
    }
}

