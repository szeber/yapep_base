<?php
declare(strict_types = 1);
/**
 * This file is part of YAPEPBase.
 *
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */
namespace YapepBase\Exception\File;

/**
 * Exception that is thrown for file not found errors.
 */
class NotFoundException extends Exception
{
    /**
     * The path and name of the file that was not found.
     *
     * @var string
     */
    protected $filename;

    /**
     * Constructor.
     *
     * @param string     $filename   Name and path of the file that was not found.
     * @param string     $message    The message for the exception.
     * @param int        $code       Code for the exception.
     * @param \Exception $previous   The previous exception.
     * @param mixed      $data       Any debugging data.
     */
    public function __construct($filename, $message = '', $code = 0, \Exception $previous = null, $data = null)
    {
        parent::__construct($message, $code, $previous, $data);

        $this->filename = $filename;
    }

    /**
     * Returns the name and path of the file that was not found.
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }
}
