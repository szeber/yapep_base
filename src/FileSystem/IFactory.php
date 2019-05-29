<?php
declare(strict_types=1);

namespace YapepBase\FileSystem;

use League\Flysystem\Filesystem;

interface IFactory
{
    public function get(string $path = '/'): Filesystem;
}
