<?php
declare(strict_types=1);

namespace YapepBase\Error\Registry;

use YapepBase\Error\Entity\Error;

class IdGenerator implements IIdGenerator
{
    /** @var int */
    private $timeoutInSeconds;

    public function __construct(int $timeoutInSeconds)
    {
        $this->timeoutInSeconds = $timeoutInSeconds;
    }

    public function generateId(Error $error): void
    {
        $message = $error->getMessage();
        $file    = $error->getFile();
        $line    = $error->getLine();

        $id = $message . $file . $line . php_uname('n');

        if ($this->timeoutInSeconds < 0) {
            $id .= uniqid('');
        } else {
            $id .= floor(time() / $this->timeoutInSeconds);
        }

        $error->setId(md5($id));
    }
}
