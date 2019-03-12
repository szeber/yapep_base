<?php
declare(strict_types = 1);

namespace YapepBase\View\Escape;

use YapepBase\Exception\ParameterException;

/**
 * Common ancestor for the different escape classes.
 */
abstract class EscapeAbstract implements IEscape
{
    protected $objectToEscape;

    abstract protected function escapeString(string $value);

    abstract protected function escapeArray(array $array);

    abstract protected function escapeBool(bool $value);

    abstract protected function escapeInt(int $value);

    abstract protected function escapeFloat(float $value);

    abstract protected function escapeNull();

    public function _escape($value)
    {
        switch (gettype($value)) {
            case 'boolean':
                return $this->escapeBool($value);
                break;

            case 'integer':
                return $this->escapeInt($value);
                break;

            case 'double':
                return $this->escapeFloat($value);
                break;

            case 'string':
                return $this->escapeString($value);
                break;

            case 'array':
                return $this->escapeArray($value);
                break;

            case 'object':
                return $this->wrapObjectForEscape($value);
                break;

            case 'NULL':
                return $this->escapeNull();
                break;

            default:
                return null;
                break;
        }
    }

    protected function wrapObjectForEscape(object $value)
    {
        foreach ($value as $whatever) {
            throw new ParameterException('No public properties are allowed on objects, please hide them and add getter methods!');
        }

        $wrappedObject                 = new static();
        $wrappedObject->objectToEscape = $value;

        return $wrappedObject;
    }

    public function __call(string $methodName, array $arguments)
    {
        return $this->_escape(call_user_func_array([$this->objectToEscape, $methodName], $arguments));
    }
}
