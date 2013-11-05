<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBase
 * @subpackage Helper
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Helper;
use YapepBase\Application;
use YapepBase\Config;

/**
 * Command output related helper functions.
 *
 * @package    YapepBase
 * @subpackage Helper
 */
class CommandOutputHelper {

	/**
	 * Runs the command and returns the command output and the separated STDERR in the outgoing parameter.
	 *
	 * Uses the "system.commandOutputHelper.work.path" config, what is describes the directory where the temporary
	 * file pointer will be added. (default => tmp)
	 *
	 * @param \YapepBase\Shell\CommandExecutor $command   The command executor object.
	 * @param string                           $stdErr    The error messages [Outgoing]
	 *
	 * @return \YapepBase\Shell\CommandOutput   The output of the command.
	 * @throws \Exception
	 */
	public function runCommandWithStdErr(\YapepBase\Shell\CommandExecutor $command, &$stdErr) {
		$dir         = Config::getInstance()->get('system.commandOutputHelper.work.path', '/tmp');
		$pipePath    = tempnam($dir, 'stderr-');
		$fileHandler = Application::getInstance()->getDiContainer()->getFileHandler();

		try {
			posix_mkfifo($pipePath, 0755);

			$command->setOutputRedirection(\YapepBase\Shell\CommandExecutor::OUTPUT_REDIRECT_STDERR, $pipePath);
			$result = $command->run();

			$stdErr = file_get_contents($pipePath);

			$fileHandler->remove($pipePath);
		}
		catch (\Exception $e) {
			$fileHandler->remove($pipePath);
			throw $e;
		}

		return $result;
	}
}