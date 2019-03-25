<?php
declare(strict_types=1);

namespace YapepBase\Response\Exception;

class HttpBodyException extends Exception
{
    const CODE_NO_CONTENT_WITH_BODY = 1;
    const CODE_PARTIAL_CONTENT_WITHOUT_RANGE_OR_DATE = 2;
    const CODE_LOCATION_HEADER_NOT_PROVIDED = 3;
    const CODE_DATE_HEADER_NOT_PROVIDED = 4;
    const CODE_WWW_AUTH_HEADER_NOT_PROVIDED = 5;
    const CODE_ALLOW_HEADER_NOT_PROVIDED = 6;

    public function __construct(int $code)
    {
        switch ($code) {
            case self::CODE_NO_CONTENT_WITH_BODY:
                $message = 'If a No Content (204) status code is returned, the response body must be empty';
                break;

            case self::CODE_PARTIAL_CONTENT_WITHOUT_RANGE_OR_DATE:
                $message = 'The Partial-Content (206) response requires a Content-Range and a Date header to be set';
                break;

            case self::CODE_LOCATION_HEADER_NOT_PROVIDED:
                $message = 'Location header is required for the given status code';
                break;

            case self::CODE_DATE_HEADER_NOT_PROVIDED:
                $message = 'Date header is required for the given status code';
                break;

            case self::CODE_WWW_AUTH_HEADER_NOT_PROVIDED:
                $message = 'Www-Authentication header is required for the given status code';
                break;

            case self::CODE_ALLOW_HEADER_NOT_PROVIDED:
                $message = 'Allow header is required for the given status code';
                break;
        }
        parent::__construct($message, $code);
    }


}
