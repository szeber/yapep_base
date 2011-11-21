<?php

namespace YapepBase;

define('BASE_DIR', realpath(dirname(dirname(__FILE__))) . '/');

require_once BASE_DIR . 'YapepBase/Autoloader/SimpleAutoloader.php';
\YapepBase\Autoloader\SimpleAutoloader::register();