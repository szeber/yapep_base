<?php
declare(strict_types=1);
/**
 * This file is part of YAPEPBase.
 *
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */
namespace YapepBase\Helper;

use YapepBase\Application;
use YapepBase\Config;
use YapepBase\Shell\CommandExecutor;

/**
 * Command output related helper functions.
 */
class CommandOutputHelper
{
    /**
     * Runs the command and returns the command output and the separated STDERR in the outgoing parameter.
     *
     * Uses the "system.commandOutputHelper.work.path" config, what is describes the directory where the temporary
     * file pointer will be added. (default => /tmp)
     *
     * @param CommandExecutor $command   The command executor object.
     * @param string          $stdErr    The error messages [Outgoing]
     *
     * @return \YapepBase\Shell\CommandOutput   The output of the command.
     * @throws \Exception
     */
    public function runCommandWithStdErr(CommandExecutor $command, &$stdErr)
    {
        $dir         = Config::getInstance()->get('system.commandOutputHelper.work.path', '/tmp');
        $pipePath    = tempnam($dir, 'stderr-');
        $fileHandler = Application::getInstance()->getDiContainer()->getFileHandler();

        try {
            posix_mkfifo($pipePath, 0755);

            $command->setOutputRedirection(CommandExecutor::OUTPUT_REDIRECT_STDERR, $pipePath);
            $result = $command->run();

            $stdErr = $fileHandler->getAsString($pipePath);

            $fileHandler->remove($pipePath);
        } catch (\Exception $e) {
            if ($fileHandler->checkIsPathExists($pipePath)) {
                $fileHandler->remove($pipePath);
            }

            throw $e;
        }

        return $result;
    }
}
