<?php
declare(strict_types = 1);
/**
 * This file is part of YAPEPBase.
 *
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */
namespace YapepBase\File;

/**
 * Abstract class what should be extended by every ResourceHandler implementations.
 */
abstract class ResourceHandlerAbstract implements IResourceHandler
{
    /**
     * Constructor. Opens a file or a URL.
     *
     * @see \YapepBase\File\IResourceHandler::openResource()
     *
     * @param string $path         Path to the file to open
     * @param int    $accessType   How to open the file. Bitmask created from the {@uses self::ACCESS_TYPE_*} constants.
     * @param bool   $isBinary     If set to TRUE the file will be opened in binary mode.
     *
     * @throws \YapepBase\Exception\File\Exception   If it fails to open the given resource.
     */
    public function __construct($path, $accessType, $isBinary = true)
    {
        $this->openResource($path, $accessType, $isBinary);
    }

    /**
     * Opens a file or a URL.
     *
     * @param string $path         Path to the file to open
     * @param int    $accessType   How to open the file. Bitmask created from the {@uses self::ACCESS_TYPE_*} constants.
     * @param bool   $isBinary     If set to TRUE the file will be opened in binary mode.
     *
     * @return void
     */
    abstract protected function openResource($path, $accessType, $isBinary = true);
}
