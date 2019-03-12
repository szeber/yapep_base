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
 * Interface for File and Stream resource handling
 */
interface IResourceHandler
{
    /** Provides write access to the opened file. */
    const ACCESS_TYPE_WRITE = 4;

    /** Truncates the opened file. */
    const ACCESS_TYPE_TRUNCATE = 2;

    /** Moves the pointer to the end of the file. */
    const ACCESS_TYPE_POINTER_AT_THE_END = 1;

    /**
     * Closes the given resource.
     *
     * @return bool   TRUE on success, FALSE on error.
     */
    public function closeResource();

    /**
     * Checks if the pointer is at the end of the file.
     *
     * @return bool   TRUE if the pointer is at the end, FALSE otherwise.
     */
    public function checkIfPointerIsAtTheEnd();

    /**
     * Gets a character from the given resource.
     *
     * @return string|bool   The character, or FALSE if the pointer is at the end of the resource.
     */
    public function getCharacter();

    /**
     * Gets a line from the given resource.
     *
     * @param int $length   If set the reading will end when the given length reached.
     *
     * @return string|bool   The line. Or FALSE if the pointer is at the end of the resource, or on error.
     */
    public function getLine($length = null);
}
