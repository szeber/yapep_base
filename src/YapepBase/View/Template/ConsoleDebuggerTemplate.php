<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   View\Template
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\View\Template;

use YapepBase\View\TemplateAbstract;
use YapepBase\View\ViewDo;

/**
 * Template for the console debugger's output
 *
 * @package    YapepBase
 * @subpackage View\Template
 */
class ConsoleDebuggerTemplate extends TemplateAbstract {

	/**
	 * The run time.
	 *
	 * @var float
	 */
	protected $runTime;

	/**
	 * Peak memory usage in bytes.
	 *
	 * @var int
	 */
	protected $peakMemory;

	/**
	 * Time milestone data.
	 *
	 * @var array
	 */
	protected $times;

	/**
	 * Memory usage data.
	 *
	 * @var array
	 */
	protected $memoryUsages;

	/**
	 * Info data.
	 *
	 * @var array
	 */
	protected $info;

	/**
	 * Error data.
	 *
	 * @var array
	 */
	protected $errors;

	/**
	 * Query data.
	 *
	 * @var array
	 */
	protected $queries;

	/**
	 * Total query times.
	 *
	 * @var array
	 */
	protected $queryTimes;

	/**
	 * Counters.
	 *
	 * @var array
	 */
	protected $counters;

	/**
	 * Constructor
	 *
	 * @param ViewDo $viewDo          The ViewDo instance to use.
	 * @param string $_runTime        Key of the run time.
	 * @param string $_peakMemory     Key of the peak memory usage.
	 * @param string $_times          Key to the time milestone data.
	 * @param string $_memoryUsages   Key to the memory usage data.
	 * @param string $_info           Key to the info data.
	 * @param string $_errors         Key to the error data.
	 * @param string $_queries        Key to the query data.
	 * @param string $_queryTimes     Key to the total query times.
	 * @param string $_counters       Key to the counters.
	 */
	public function __construct(
		ViewDo $viewDo, $_runTime, $_peakMemory, $_times, $_memoryUsages, $_info, $_errors,
		$_queries, $_queryTimes, $_counters
	) {
		$this->setViewDo($viewDo);

		$this->runTime      = $this->get($_runTime);
		$this->peakMemory   = $this->get($_peakMemory);
		$this->times        = $this->get($_times);
		$this->memoryUsages = $this->get($_memoryUsages);
		$this->info         = $this->get($_info);
		$this->errors       = $this->get($_errors);
		$this->queries      = $this->get($_queries);
		$this->queryTimes   = $this->get($_queryTimes);
		$this->counters     = $this->get($_counters);
	}

	/**
	 * Does the actual rendering.
	 *
	 * @return void
	 */
	protected function renderContent() {
		// TODO: Implement renderContent() method.
	}

}