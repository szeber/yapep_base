<?php

namespace YapepBase\Database;


use PDO;
use YapepBase\Mock\Database\DbConnectionMock;

class DbConnectionTest extends \YapepBase\BaseTest {

	public function paramTypeProvider() {
		return array(
			'integer given'           => array(11, 11, PDO::PARAM_INT),
			'float given'             => array(0.87, '0.87', PDO::PARAM_STR),
			'integer as string given' => array('11', '11', PDO::PARAM_STR),
			'float as string given'   => array('0.87', '0.87', PDO::PARAM_STR),
			'null given'              => array(null, null, PDO::PARAM_NULL),
			'true given'              => array(true, true, PDO::PARAM_BOOL),
			'false given'             => array(false, false, PDO::PARAM_BOOL),
		);
	}

	/**
	 * @param $param
	 * @param $expectedCastedParam
	 * @param $expectedType
	 *
	 * @dataProvider paramTypeProvider
	 */
	public function testGetParamType_shouldReturnProperTypeAndCastGivenValue($param, $expectedCastedParam, $expectedType) {
		$type = (new DbConnectionMock())->getParamType($param);

		$this->assertSame($expectedCastedParam, $param);
		$this->assertSame($expectedType, $type);
	}

}

