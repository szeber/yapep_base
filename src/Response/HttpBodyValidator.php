<?php
declare(strict_types=1);

namespace YapepBase\Response;

use YapepBase\Response\Entity\HeaderContainer;
use YapepBase\Response\Exception\HttpBodyException;

class HttpBodyValidator
{
    /** @var HeaderContainer[] */
    protected $headers = [];

    /**
     * @param HeaderContainer $headers
     */
    public function __construct(array $headers)
    {
        $this->headers = $headers;
    }

    /**
     * @throws HttpBodyException
     */
    public function validate(int $statusCode, string $renderedBody): void
    {
        switch ($statusCode) {
            case 204:
                if (!empty($renderedBody)) {
                    throw new HttpBodyException(HttpBodyException::CODE_NO_CONTENT_WITH_BODY);
                }
                break;

            case 206:
                if (!$this->hasHeader('Content-Range') || !$this->hasHeader('Date')) {
                    throw new HttpBodyException(HttpBodyException::CODE_PARTIAL_CONTENT_WITHOUT_RANGE_OR_DATE);
                }
                break;

            case 301:
            case 302:
            case 303:
            case 305:
            case 307:
                if (!$this->hasHeader('Location')) {
                    throw new HttpBodyException(HttpBodyException::CODE_LOCATION_HEADER_NOT_PROVIDED);
                }
                break;

            case 304:
                if (!$this->hasHeader('Date')) {
                    throw new HttpBodyException(HttpBodyException::CODE_DATE_HEADER_NOT_PROVIDED);
                }
                break;

            case 401:
                if (!$this->hasHeader('WWW-Authenticate')) {
                    throw new HttpBodyException(HttpBodyException::CODE_WWW_AUTH_HEADER_NOT_PROVIDED);
                }
                break;

            case 405:
                if (!$this->hasHeader('Allow')) {
                    throw new HttpBodyException(HttpBodyException::CODE_ALLOW_HEADER_NOT_PROVIDED);
                }
                break;
        }
    }

    protected function hasHeader(string $headerName): bool
    {
        return isset($this->headersByName[$headerName]);
    }
}
