<?php
declare(strict_types = 1);

namespace YapepBase\Exception;

class RuntimeException extends \RuntimeException implements IException
{
    /**
     * Any debugging data.
     *
     * @var mixed
     */
    protected $data;

    /**
     * Constructor.
     *
     * @param string     $message  The exception message.
     * @param int        $code     The exception code.
     * @param \Exception $previous Previous exceptions.
     * @param mixed      $data     Any debugging data.
     */
    public function __construct($message = '', $code = 0, \Exception $previous = null, $data = null)
    {
        parent::__construct($message, $code, $previous);
        $this->data = $data;
    }

    /**
     * Returns the debugging data if set.
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }
}
