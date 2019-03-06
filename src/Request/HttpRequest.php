<?php
declare(strict_types=1);

namespace YapepBase\Request;

use Emul\Server\ServerData;
use YapepBase\Application;
use YapepBase\Helper\ArrayHelper;
use YapepBase\Request\Entity\IParams;
use YapepBase\Request\Entity\IFiles;
use YapepBase\Request\Entity\CustomParams;

/**
 * Stores the details for the current request.
 */
class HttpRequest implements IRequest
{
    const METHOD_HTTP_GET = 'GET';
    const METHOD_HTTP_POST = 'POST';
    const METHOD_HTTP_PUT = 'PUT';
    const METHOD_HTTP_HEAD = 'HEAD';
    const METHOD_HTTP_OPTIONS = 'OPTIONS';
    const METHOD_HTTP_DELETE = 'DELETE';

    const PROTOCOL_HTTP = 'http';
    const PROTOCOL_HTTPS = 'https';
    const PROTOCOL_CLI = 'cli';

    /** @var IParams */
    protected $queryParams;
    /** @var IParams */
    protected $postParams;
    /** @var IParams */
    protected $cookies;
    /** @var IParams */
    protected $envParams;
    /** @var IParams */
    protected $inputParams;
    /** @var IFiles */
    protected $files;
    /** @var ServerData */
    protected $server;
    /** @var CustomParams */
    protected $customParams;
    /** @var array */
    protected $acceptedContentTypes = [];
    /** @var string */
    protected $targetUri   = '';

    public function __construct(
        IParams $queryParams,
        IParams $postParams,
        IParams $cookies,
        IParams $envParams,
        IParams $inputParams,
        IFiles $files,
        ServerData $server
    ) {
        $this->queryParams  = $queryParams;
        $this->postParams   = $postParams;
        $this->cookies      = $cookies;
        $this->envParams    = $envParams;
        $this->inputParams  = $inputParams;
        $this->files        = $files;
        $this->server       = $server;
        $this->customParams = new CustomParams([]);

        list($this->targetUri) = explode('?', $this->server->getRequestUri(), 2);
    }

    public function getQueryParams(): IParams
    {
        return $this->queryParams;
    }

    public function getPostParams(): IParams
    {
        return $this->postParams;
    }

    public function getCookies(): IParams
    {
        return $this->cookies;
    }

    public function getEnvParams(): IParams
    {
        return $this->envParams;
    }

    public function getInputParams(): IParams
    {
        return $this->inputParams;
    }

    public function getFiles(): IFiles
    {
        return $this->files;
    }

    public function getServer(): ServerData
    {
        return $this->server;
    }

    public function getCustomParams(): CustomParams
    {
        return $this->customParams;
    }

    public function getRequestParamAsInt(string $name, ?int $default = null): ?int
    {
        $queryParam  = $this->queryParams->getAsInt($name);
        $postParam   = $this->postParams->getAsInt($name);
        $customParam = $this->customParams->getAsInt($name);

        return $queryParam ?? $postParam ?? $customParam ?? $default;
    }

    public function getRequestParamAsString(string $name, ?string $default = null): ?string
    {
        $queryParam  = $this->queryParams->getAsString($name);
        $postParam   = $this->postParams->getAsString($name);
        $customParam = $this->customParams->getAsString($name);

        return $queryParam ?? $postParam ?? $customParam ?? $default;
    }

    public function getRequestParamAsFloat(string $name, ?float $default = null): ?float
    {
        $queryParam  = $this->queryParams->getAsFloat($name);
        $postParam   = $this->postParams->getAsFloat($name);
        $customParam = $this->customParams->getAsFloat($name);

        return $queryParam ?? $postParam ?? $customParam ?? $default;
    }

    public function getRequestParamAsArray(string $name, ?array $default = []): ?array
    {
        $queryParam  = $this->queryParams->getAsArray($name);
        $postParam   = $this->postParams->getAsArray($name);
        $customParam = $this->customParams->getAsArray($name);

        return $queryParam ?? $postParam ?? $customParam ?? $default;
    }

    public function getRequestParamAsBool(string $name, ?bool $default = null): ?bool
    {
        $queryParam  = $this->queryParams->getAsBool($name);
        $postParam   = $this->postParams->getAsBool($name);
        $customParam = $this->customParams->getAsBool($name);

        return $queryParam ?? $postParam ?? $customParam ?? $default;
    }

    public function getTarget(): string
    {
        return $this->targetUri;
    }

    public function getMethod(): string
    {
        return (string)$this->server->getRequestMethod();
    }

    /**
     * Parses the accept setHeader and returns an array with all the parsed accepted content types.
     *
     * The returned array has the following keys:
     * <ul>
     *   <li>mimeType: The full MIME type</li>
     *   <li>type:     The type part of the MIME type</li>
     *   <li>subType:  The subtype part of the MIME type</li>
     *   <li>params:   An associative array with all the params. The key is the name of the param, the value is
     *                 it's value.</li>
     *   <li>original: The original, unparsed content type item</li>
     * </ul>
     *
     * @return array   The parsed array
     */
    public function getAcceptedContentTypes()
    {
        if (is_array($this->acceptedContentTypes)) {
            return $this->acceptedContentTypes;
        }
        $header = trim($this->getServer('HTTP_ACCEPT', ''));
        if (empty($header)) {
            return [];
        }

        $types                      = explode(',', $header);
        $this->acceptedContentTypes = [];
        foreach ($types as $type) {
            $parsed = $this->parseContentType($type);
            if (false !== $parsed) {
                $this->acceptedContentTypes[] = $parsed;
            }
        }

        return $this->acceptedContentTypes;
    }

    /**
     * Parses a content type string and returns an associative array with the parsed values
     *
     * The returned array has the following keys:
     * <ul>
     *   <li>mimeType: The full MIME type</li>
     *   <li>type:     The type part of the MIME type</li>
     *   <li>subType:  The subtype part of the MIME type</li>
     *   <li>params:   An associative array with all the params. The key is the name of the param, the value is
     *                 it's value.</li>
     *   <li>original: The original, unparsed content type item</li>
     * </ul>
     *
     * @param string $type The content type {@uses \YapepBase\Mime\MimeType::*}
     *
     * @return array|bool   The array with the values or FALSE if it is invalid
     */
    protected function parseContentType($type)
    {
        $parts     = explode(';', trim($type));
        $mime      = trim(array_shift($parts));
        $mimeParts = explode('/', $mime, 2);

        if (empty($mime) || 2 != count($mimeParts) || empty($mimeParts[0]) || empty($mimeParts[1])) {
            return false;
        }

        $params = [];

        foreach ($parts as $part) {
            $paramParts = explode('=', trim($part), 2);
            if (2 != count($paramParts)) {
                continue;
            }
            $params[trim($paramParts[0])] = trim($paramParts[1]);
        }

        if (isset($params['q'])) {
            $params['q'] = (float)$params['q'];
        } else {
            $params['q'] = 1.0;
        }

        return [
            'mimeType' => $mime,
            'type' => trim($mimeParts[0]),
            'subType' => trim($mimeParts[1]),
            'params' => $params,
            'original' => trim($type),
        ];
    }

    /**
     * Returns the accepted content types in the order of preference by the client.
     *
     * The returned values have the same structure as the HttpRequest::getAcceptedContentTypes method.
     *
     * @return array
     *
     * @see HttpRequest::getAcceptedContentTypes()
     */
    public function getAcceptedContentTypesByPreference()
    {
        $sortFunction = function ($a, $b) {
            // Compare by preference
            if ($a['params']['q'] != $b['params']['q']) {
                return ($a['params']['q'] < $b['params']['q'] ? -1 : 1);
            }

            // Compare by type specificity
            if ($a['type'] == '*' || $b['type'] == '*') {
                if ($a['type'] == '*') {
                    if ($b['type'] == '*') {
                        return 0;
                    }
                    return -1;
                }
                // A is not *, B is, A is greater
                return 1;
            }

            if ($a['subType'] == '*' || $b['subType'] == '*') {
                if ($a['subType'] == '*') {
                    if ($b['subType'] == '*') {
                        return 0;
                    }
                    return -1;
                }
                return 1;
            }

            if (count($a['params']) == count($b['params'])) {
                return 0;
            }

            return (count($a['params']) < count($b['params']) ? -1 : 1);
        };

        $acceptedTypes = $this->getAcceptedContentTypes();

        usort($acceptedTypes, $sortFunction);

        return array_reverse($acceptedTypes);
    }

    /**
     * Returns TRUE if the checked content type matches the accepted content type.
     *
     * The provided parameters must have the structure of the return value of the HttpRequest::parseContentType()
     * method.
     *
     * @param array $checkedContentType The content type to check.
     * @param array $acceptedType       The content type to check against. May contain wildcards.
     * @param int   $specificity        The level of specificity (outgoing parameter).
     *
     * @return bool
     *
     * @see HttpRequest::parseContentType()
     */
    protected function checkIfContentTypesMatch(array $checkedContentType, array $acceptedType, &$specificity = null)
    {
        $specificity = 0;

        if ($acceptedType['type'] == '*') {
            return true;
        }

        if ($checkedContentType['type'] == $acceptedType['type'] && $acceptedType['subType'] == '*') {
            $specificity = 1;
            return true;
        }

        unset($acceptedType['params']['q']);

        if ($checkedContentType['type'] == $acceptedType['type'] && $checkedContentType['subType'] == $acceptedType['subType']) {
            if (empty($acceptedType['params'])) {
                $specificity = 2;
                return true;
            }
            foreach ($acceptedType['params'] as $key => $value) {
                if (!isset($checkedContentType['params'][$key]) || $checkedContentType['params'][$key] != $value) {
                    return false;
                }
            }
            $specificity = count($acceptedType['params']) + 2;
            return true;
        }

        return false;
    }

    /**
     * Returns TRUE if the provided content type is one of the content types accepted by the client, FALSE otherwise.
     *
     * IF there was no Accept setHeader in the request, it will always return TRUE for a valid content type.
     *
     * @param string $contentType The content type {@uses \YapepBase\Mime\MimeType::*}
     *
     * @return bool
     */
    public function checkIfContentTypeIsPreferred($contentType)
    {
        $parsed = $this->parseContentType($contentType);
        if (false === $parsed) {
            return false;
        }

        $acceptedTypes = $this->getAcceptedContentTypes();

        if (empty($acceptedTypes)) {
            return true;
        }

        foreach ($acceptedTypes as $acceptedType) {
            if ($this->checkIfContentTypesMatch($parsed, $acceptedType)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns the preference value of the client for the provided content type.
     *
     * If the returned value is 0.0, the client does not accept the content type. If just checking if the client
     * accepts the content type use HttpRequest::checkIfContentTypeIsPreferred() instead, since it's faster.
     * If there was no Accept setHeader in the request, it will return 1.0 for a valid content type.
     *
     * @param string $contentType The content type {@uses \YapepBase\Mime\MimeType::*}
     *
     * @return float
     *
     * @see HttpRequest::checkIfContentTypeIsPreferred()
     */
    public function getContentTypePreferenceValue($contentType)
    {
        $parsed = $this->parseContentType($contentType);
        if (false === $parsed) {
            return 0.0;
        }

        if (isset($parsed['params']['q'])) {
            unset($parsed['params']['q']);
        }

        $preferenceValue       = 0.0;
        $preferenceSpecificity = 0;
        $specificity           = 0;

        $acceptedTypes = $this->getAcceptedContentTypes();

        if (empty($acceptedTypes)) {
            return 1.0;
        }

        foreach ($acceptedTypes as $acceptedType) {
            if ($this->checkIfContentTypesMatch($parsed, $acceptedType,
                    $specificity) && ($specificity >= $preferenceSpecificity || ($specificity == $preferenceSpecificity && $acceptedType['params']['q'] > $preferenceValue))) {
                $preferenceValue       = $acceptedType['params']['q'];
                $preferenceSpecificity = $specificity;
            }
        }

        return $preferenceValue;
    }

    public function getProtocol(): string
    {
        return $this->server->isHttps()
            ? self::PROTOCOL_HTTPS
            : self::PROTOCOL_HTTP;
    }

    protected function getArrayHelper(): ArrayHelper
    {
        return Application::getInstance()->getDiContainer()->get(ArrayHelper::class);
    }
}
