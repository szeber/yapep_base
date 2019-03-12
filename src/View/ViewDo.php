<?php
declare(strict_types = 1);
/**
 * This file is part of YAPEPBase.
 *
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */
namespace YapepBase\View;

use YapepBase\Exception\Exception;
use YapepBase\Exception\ParameterException;
use YapepBase\Mime\MimeType;

/**
 * A simple data storage object used by the View layer.
 */
class ViewDo
{
    /**
     * The content type what should be considered before escaping the data. {@uses MimeType::*}
     *
     * @var string
     */
    protected $contentType;

    /**
     * Stores the raw data.
     *
     * @var array
     */
    protected $dataRaw = [];

    /**
     * Stores the escaped data.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Constructor
     *
     * @param string $contentType   The content type for the response. {@uses MimeType::*}
     */
    public function __construct($contentType)
    {
        $this->contentType = $contentType;
    }

    /**
     * Returns the the value registered to the given key.
     *
     * @param string $key   The name of the key.
     * @param bool   $raw   if TRUE it will return the raw (unescaped) data.
     *
     * @return mixed   The data stored with the given key.
     *
     * @todo Review error handling. We should throw exceptions where possible. [szeber]
     */
    final public function get($key, $raw = false)
    {
        if (empty($key)) {
            trigger_error('Empty key', E_USER_NOTICE);

            return null;
        }

        $keyParts = explode('.', $key);

        $itemKey = array_shift($keyParts);

        if (!array_key_exists($itemKey, $this->dataRaw)) {
            trigger_error('Not defined value: ' . $key, E_USER_NOTICE);

            return null;
        }

        // Storing a copy by reference which will be used
        if ($raw) {
            $target = &$this->dataRaw[$itemKey];
        } else {
            if (!array_key_exists($itemKey, $this->data)) {
                try {
                    $this->data[$itemKey] = $this->escape($this->dataRaw[$itemKey]);
                } catch (ParameterException $e) {
                    trigger_error('Tried to access the key: ' . $key . ', but it can not be escaped', E_USER_NOTICE);

                    return null;
                }
            }
            $target = &$this->data[$itemKey];
        }

        if (empty($keyParts)) {
            return $target;
        }

        // TODO: This part should be extracted [emul]

        // Processing all of the keys except the last one
        for ($i = 0; $i < count($keyParts) - 1; $i++) {
            // If the key exists and its value is array
            if (isset($target[$keyParts[$i]]) && is_array($target[$keyParts[$i]])) {
                // Then we overwrite the stores copy with the new child element
                $target = &$target[$keyParts[$i]];
            } else {
                // If it doesn't exist, or its not an array, it means that we can't go further,
                // so the requested key does not exist.
                trigger_error('Not defined value: ' . $key, E_USER_NOTICE);

                return null;
            }
        }

        // We reached the desired depth
        // @todo shouln'd we use array_pop here?
        return $target[array_pop($keyParts)];
    }

    /**
     * Stores one ore more value(s).
     *
     * @param string $nameOrData   The name of the key, or the storable data in an associative array.
     * @param mixed  $value        The value.
     *
     * @throws \YapepBase\Exception\Exception   If the key already exist.
     *
     * @return void
     */
    public function set($nameOrData, $value = null)
    {
        if (is_array($nameOrData)) {
            foreach ($nameOrData as $key => $value) {
                $this->set($key, $value);
            }
        } else {
            if (array_key_exists($nameOrData, $this->dataRaw)) {
                throw new Exception('Key already exist: ' . $nameOrData);
            }
            $this->dataRaw[$nameOrData] = $value;
        }
    }

    /**
     * Clears all the stored data.
     *
     * @return void
     */
    public function clear()
    {
        $this->data    = [];
        $this->dataRaw = [];
    }

    /**
     * Checks the given key if it has a value.
     *
     * @param string $key          The name of the key.
     * @param bool   $checkIsSet   If TRUE it checks the existense of the key.
     *
     * @return bool   FALSE if it has a value/exist, TRUE if not.
     */
    public function checkIsEmpty($key, $checkIsSet = false)
    {
        if (empty($key)) {
            trigger_error('Empty key', E_USER_NOTICE);

            return true;
        }
        $keyParts = explode('.', $key);

        // Storing a copy by refence which will be used
        $target = &$this->dataRaw;

        // Processing all of the keys except the last one
        for ($i = 0; $i < count($keyParts) - 1; $i++) {
            // If the key exists and its value is array
            if (isset($target[$keyParts[$i]]) && is_array($target[$keyParts[$i]])) {
                // Then we overwrite the stores copy with the new child element
                $target = &$target[$keyParts[$i]];
            } else {
                // If it doesn't exist, or its not an array, it means that we can't go further,
                // so the requested key does not exist.
                return true;
            }
        }

        return $checkIsSet
            ? !array_key_exists($keyParts[count($keyParts) - 1], $target)
            : empty($target[$keyParts[count($keyParts) - 1]]);
    }

    /**
     * Checks if the value is an array.
     *
     * @param string $key   The name of the key.
     *
     * @return bool   TRUE if its an array, FALSE if not.
     */
    public function checkIsArray($key)
    {
        $keyParts = explode('.', $key);

        // Storing a copy by refence which will be used
        $target = &$this->dataRaw;

        // Processing all of the keys except the last one
        for ($i = 0; $i < count($keyParts) - 1; $i++) {
            // If the key exists and its value is array
            if (isset($target[$keyParts[$i]]) && is_array($target[$keyParts[$i]])) {
                // Then we overwrite the stores copy with the new child element
                $target = &$target[$keyParts[$i]];
            } else {
                // If it doesn't exist, or its not an array, it means that we can't go further,
                // so the requested key does not exist.
                return false;
            }
        }

        return isset($target[$keyParts[count($keyParts) - 1]])
            ? is_array($target[$keyParts[count($keyParts) - 1]])
            : false;
    }

    /**
     * Escapes the value based on the response content type.
     *
     * @param mixed $value   The value to escape.
     *
     * @return mixed   The escaped value
     *
     * @throws \YapepBase\Exception\ParameterException   If a non-escapable type is passed.
     */
    public function escape($value)
    {
        switch ($this->contentType) {
            case MimeType::HTML:
            default:
                return $this->escapeForHtml($value);
                break;
        }
    }

    /**
     * Escapes the given parameter to HTML response. It escapes arrays recursively
     *
     * @param mixed $value   The data wat should be escaped.
     *
     * @return mixed   The escaped value.
     *
     * @throws \YapepBase\Exception\ParameterException   If a non-escapable type is passed.
     */
    protected function escapeForHtml($value)
    {
        switch (gettype($value)) {
            case 'string':
                return htmlspecialchars($value);
                break;

            case 'array':
                foreach ($value as $elementKey => $elementValue) {
                    $value[$elementKey] = $this->escapeForHtml($elementValue);
                }

                return $value;
                break;

            case 'object':
                if (
                    ($value instanceof \ArrayAccess)
                    &&
                    (
                        ($value instanceof \Iterator)
                        ||
                        ($value instanceof \IteratorAggregate)
                    )
                ) {
                    $clonedObject = clone $value;
                    foreach ($clonedObject as $elementKey => $elementValue) {
                        $clonedObject[$elementKey] = $this->escapeForHtml($elementValue);
                    }

                    return $clonedObject;
                } elseif (method_exists($value, '__toString')) {
                    return $this->escapeForHtml((string)$value);
                } else {
                    throw new ParameterException('Unable to escape objects');
                }
                break;

            case 'unknown type':
            case 'resource':
                throw new ParameterException('Unable to escape resources and unknown types');
                break;

            default:
                return $value;
                break;
        }
    }

    /**
     * Returns all the data set as an associative array
     *
     * @param bool   $raw   If TRUE it will return the raw (unescaped) data.
     *
     * @return array
     */
    public function toArray($raw = false)
    {
        $result = [];

        if ($raw) {
            $result = $this->dataRaw;
        } else {
            foreach (array_keys($this->dataRaw) as $key) {
                $result[$key] = $this->get($key);
            }
        }

        return $result;
    }
}
