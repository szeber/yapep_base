<?php
declare(strict_types=1);

namespace YapepBase\Exception;

class Exception extends \Exception implements IException
{
    /**
     * Any debugging data.
     *
     * @var mixed
     */
    protected $data;

    public function __construct(string $message = '', int $code = 0, \Exception $previous = null, $data = null)
    {
        parent::__construct($message, $code, $previous);
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }
}
