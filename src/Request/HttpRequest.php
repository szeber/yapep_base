<?php
declare(strict_types=1);

namespace YapepBase\Request;

use Emul\Server\ServerData;
use YapepBase\Application;
use YapepBase\DataObject\UploadedFileDo;
use YapepBase\Helper\ArrayHelper;
use YapepBase\Request\Entity\Cookie;
use YapepBase\Request\Entity\Env;
use YapepBase\Request\Entity\File;
use YapepBase\Request\Entity\Input;
use YapepBase\Request\Entity\Post;
use YapepBase\Request\Entity\Query;
use YapepBase\Request\Entity\Custom;

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

    /** @var ServerData */
    protected $server;
    /** @var array */
    protected $acceptedContentTypes = [];
    /** @var string */
    protected $targetUri   = '';

    public function __construct(
        array $queryParams,
        array $postParams,
        array $cookies,
        array $server,
        array $envParams,
        array $files,
        array $inputParams
    ) {
        $this->queryParams = new Query($queryParams);
        $this->postParams  = new Post($postParams);
        $this->cookies     = new Cookie($cookies);
        $this->envParams   = new Env($envParams);
        $this->inputParams = new Input($inputParams);
        $this->files       = new File($files);
        $this->server      = new ServerData($server);
        $this->routeParams = new Custom([]);

        list($this->targetUri) = explode('?', $this->server->getRequestUri(), 2);
    }

    public function getFile(string $name): ?UploadedFileDo
    {
        if (isset($this->files[$name]) && isset($this->files[$name]['error']) && UPLOAD_ERR_NO_FILE != $this->files[$name]['error']) {
            return new UploadedFileDo($this->files[$name]);
        }

        return null;
    }

    protected function getMergedRequestParams(): array
    {
        return array_merge($this->inputParams, $this->postParams, $this->queryParams, $this->routeParams);
    }


    /**
     * Returns TRUE if the specified upload is in the request.
     *
     * This method will return TRUE, if the specified upload is in the request, but there was no uploaded file sent.
     */
    public function hasFile(string $name): bool
    {
        return isset($this->files[$name]) && isset($this->files[$name]['error']) && UPLOAD_ERR_NO_FILE != $this->files[$name]['error'];
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
     * Sets a route param
     *
     * @param string $name  Name of the param.
     * @param mixed  $value Value of the param.
     *
     * @return void
     */
    public function setParam(string $name, $value)
    {
        $this->routeParams[$name] = $value;
    }

    /**
     * Returns TRUE if the request was made as an AJAX request.
     *
     * @return bool
     */
    public function isAjaxRequest()
    {
        return (!empty($this->server['HTTP_X_REQUESTED_WITH']) && 'xmlhttprequest' == strtolower($this->server['HTTP_X_REQUESTED_WITH']));
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

    /**
     * @inheritdoc
     */
    public function getProtocol(): string
    {
        return ($this->getServer('HTTPS', 'off') == 'on' ? self::PROTOCOL_HTTPS : self::PROTOCOL_HTTP);
    }

    protected function getArrayHelper(): ArrayHelper
    {
        return Application::getInstance()->getDiContainer()->get(ArrayHelper::class);
    }
}
