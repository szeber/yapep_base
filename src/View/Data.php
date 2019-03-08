<?php
declare(strict_types=1);

namespace YapepBase\View;

use YapepBase\Exception\ParameterException;
use YapepBase\View\Escape\IEscape;

/**
 * A simple data storage object used by the View layer.
 */
class Data
{
    /** @var array */
    protected $dataRaw = [];

    /** @var IEscape */
    protected $htmlEscaper;

    /** @var IEscape */
    protected $javascriptEscaper;

    public function __construct(IEscape $htmlEscaper, IEscape $javascriptEscaper)
    {
        $this->htmlEscaper       = $htmlEscaper;
        $this->javascriptEscaper = $javascriptEscaper;
    }

    /**
     * Sets the given value with the given key to the object.
     *
     * @throws ParameterException   If the given key already exists.
     */
    public function set(string $key, $value): void
    {
        if ($this->has($key)) {
            throw new ParameterException('Key already exist: ' . $key);
        }

        $this->dataRaw[$key] = $value;
    }

    /**
     * Sets the elements of the given array by their key
     *
     * @throws ParameterException    If any of the keys already exist.
     */
    public function setMass(array $valuesByName): void
    {
        foreach ($valuesByName as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Returns the value escaped for HTML
     */
    public function getForHtml(string $key)
    {
        return $this->htmlEscaper->__escape($this->getRaw($key));
    }

    /**
     * Returns the value escaped for Javascript
     */
    public function getForJavascript(string $key)
    {
        return $this->javascriptEscaper->__escape($this->getRaw($key));
    }

    /**
     * Returns the value escaped for HTML
     *
     * !!! Warning the result of this method is unescaped which poses threat on security and reliability as well !!!
     */
    public function getRaw(string $key)
    {
        if (!$this->has($key)) {
            throw new ParameterException('The given key does not exist');
        }
        return $this->dataRaw[$key];
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->dataRaw);
    }

    public function clear(): void
    {
        $this->dataRaw = [];
    }
}
