<?php
declare(strict_types=1);

namespace YapepBase\FileSystem;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

class Factory implements IFactory
{
    public function get(string $path = '/'): Filesystem
    {
        return new Filesystem(new Local($path));
    }
}
