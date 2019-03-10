<?php
declare(strict_types=1);

namespace YapepBase\View\Data;

use YapepBase\Exception\ParameterException;
use YapepBase\View\Escape\IEscape;

/**
 * A simple data storage object used by the View layer.
 */
class Data implements ICanEscape
{
    const ESCAPED_KEY_HTML       = 'html';
    const ESCAPED_KEY_JAVASCRIPT = 'javascript';

    /** @var array */
    protected $dataRaw = [];

    /** @var array */
    protected $dataEscaped = [];

    /** @var IEscape */
    protected $htmlEscaper;

    /** @var IEscape */
    protected $javascriptEscaper;

    public function __construct(IEscape $htmlEscaper, IEscape $javascriptEscaper)
    {
        $this->htmlEscaper       = $htmlEscaper;
        $this->javascriptEscaper = $javascriptEscaper;
    }

    public function set(string $key, $value): void
    {
        if ($this->has($key)) {
            throw new ParameterException('Key already exist: ' . $key);
        }

        $this->dataRaw[$key] = $value;
    }

    public function setMass(array $valuesByName): void
    {
        foreach ($valuesByName as $key => $value) {
            $this->set($key, $value);
        }
    }

    public function getForHtml(string $key)
    {
        if (isset($this->dataEscaped[self::ESCAPED_KEY_HTML][$key])) {
            return $this->dataEscaped[self::ESCAPED_KEY_HTML][$key];
        }

        $result = $this->htmlEscaper->__escape($this->getRaw($key));
        $this->dataEscaped[self::ESCAPED_KEY_HTML][$key] = $result;

        return $result;
    }

    public function getForJavascript(string $key)
    {
        if (isset($this->dataEscaped[self::ESCAPED_KEY_JAVASCRIPT][$key])) {
            return $this->dataEscaped[self::ESCAPED_KEY_JAVASCRIPT][$key];
        }

        $result = $this->javascriptEscaper->__escape($this->getRaw($key));
        $this->dataEscaped[self::ESCAPED_KEY_JAVASCRIPT][$key] = $result;

        return $result;
    }

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
