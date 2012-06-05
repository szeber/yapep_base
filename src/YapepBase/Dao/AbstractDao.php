<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Dao
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Dao;

/**
 * Base abstract DAO which should be extended by every DAO class.
 *
 * @package    YapepBase
 * @subpackage Dao
 */
abstract class AbstractDao {
	/** Interval unit for day. */
	const INTERVAL_UNIT_DAY = 'day';
	/** Interval unit for month. */
	const INTERVAL_UNIT_MONTH = 'month';
	/** Interval unit for year. */
	const INTERVAL_UNIT_YEAR = 'year';
}