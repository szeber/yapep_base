<?php

namespace YapepBase\Test\DependencyInjection;

use YapepBase\DependencyInjection\Container;
use YapepBase\Test\Mock\DependencyInjection\ObjectMock;
use YapepBase\Exception\ParameterException;


class ContainerTest extends \PHPUnit_Framework_TestCase {

	public function testSetWithString() {
		$container = new Container();
		$container['param'] = 'value';

		$this->assertEquals('value', $container['param']);
	}

	public function testSetWithClosure() {
		$container = new Container();
		$container['test'] = function () {
			return new ObjectMock();
		};

		$this->assertInstanceOf('YapepBase\Test\Mock\DependencyInjection\ObjectMock', $container['test']);
	}

	public function testStoredObjectsDifference() {
		$container = new Container();
		$container['test'] = function () {
			return new ObjectMock();
		};

		$objectOne = $container['test'];
		$this->assertInstanceOf('YapepBase\Test\Mock\DependencyInjection\ObjectMock', $objectOne);

		$objectTwo = $container['test'];
		$this->assertInstanceOf('YapepBase\Test\Mock\DependencyInjection\ObjectMock', $objectTwo);

		$this->assertNotSame($objectOne, $objectTwo);
	}

	public function testShouldPassContainerAsParameter() {
		$container = new Container();

		$container['test'] = function () {
			return new ObjectMock();
		};
		$container['container'] = function ($container) {
			return $container;
		};

		$this->assertNotSame($container, $container['test']);
		$this->assertSame($container, $container['container']);
	}

	public function testIsset() {
		$container = new Container();
		$container['param'] = 'value';
		$container['test'] = function () {
			return new ObjectMock();
		};

		$this->assertTrue(isset($container['param']));
		$this->assertTrue(isset($container['test']));
		$this->assertFalse(isset($container['non_existent']));
	}

	public function testConstructorInjection () {
		$params = array('param' => 'value');
		$container = new Container($params);

		$this->assertSame($params['param'], $container['param']);
	}

	/**
	 * @expectedException \YapepBase\Exception\ParameterException
	 * @expectedExceptionMessage Unknown key: test
	 */
	public function testOffsetGetValidatesKeyIsPresent() {
		$container = new Container();
		echo $container['test'];
	}

	public function testOffsetGetHonorsNullValues() {
		$container = new Container();
		$container['test'] = null;
		$this->assertNull($container['test']);
	}

	public function testUnset() {
		$container = new Container();
		$container['param'] = 'value';
		$container['test'] = function () {
			return new ObjectMock();
		};

		unset($container['param'], $container['test']);
		$this->assertFalse(isset($container['param']));
		$this->assertFalse(isset($container['test']));
	}

	public function testShare() {
		$container = new Container();
		$container['shared_test'] = $container->share(function () {
			return new ObjectMock();
		});

		$objectOne = $container['shared_test'];
		$this->assertInstanceOf('YapepBase\Test\Mock\DependencyInjection\ObjectMock', $objectOne);

		$objectTwo = $container['shared_test'];
		$this->assertInstanceOf('YapepBase\Test\Mock\DependencyInjection\ObjectMock', $objectTwo);

		$this->assertSame($objectOne, $objectTwo);
	}

	public function testProtect() {
		$container = new Container();
		$callback = function () { return 'test'; };
		$container['protected'] = $container->protect($callback);

		$this->assertSame($callback, $container['protected']);
	}

	public function testGlobalFunctionNameAsParameterValue() {
		$container = new Container();
		$container['global_function'] = 'strlen';
		$this->assertSame('strlen', $container['global_function']);
	}

	public function testGetRaw() {
		$container = new Container();
		$container['test'] = $definition = function () { return 'test'; };
		$this->assertSame($definition, $container->getRaw('test'));
	}

	public function testRawHonorsNullValues() {
		$container = new Container();
		$container['test'] = null;
		$this->assertNull($container->getRaw('test'));
	}

	/**
	 * @expectedException \YapepBase\Exception\ParameterException
	 * @expectedExceptionMessage Unknown key: test
	 */
	public function testGetRawValidatesKeyIsPresent() {
		$container = new Container();
		$container->getRaw('test');
	}
}
