<?php

require 'bootstrap.php';

$commandExec = new \YapepBase\Shell\CommandExecutor();
$commandExec->setCommand('lss');
\YapepBase\Config::getInstance()->set('/testPipe', 'system.commandOutputHelper.work.path');

$result = \YapepBase\Helper\CommandOutputHelper::getStdErrFromOutput($commandExec, $stdErr);

echo '<pre>';
var_dump($result, $stdErr);
echo '</pre>';
exit;
