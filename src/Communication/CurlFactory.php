<?php
declare(strict_types = 1);
/**
 * This file is part of YAPEPBase.
 *
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */
namespace YapepBase\Communication;

/**
 * Factory class for CurlHttpWrapper instances
 *
 *
 * @deprecated Will be removed in the next version, use SystemContainer::getCurlHttpRequest() instead.
 */
class CurlFactory
{
    /**
     * Returns a new CurlHttpWrapper instance.
     *
     * @param string $method                              The request method {@uses self::METHOD_*}.
     * @param string $url                                 The URL of the request.
     * @param array  $parameters                          The GET or POST parameters for the request.
     * @param array  $additionalHeaders                   Additional HTTP headers for the request.
     * @param array  $extraOptions                        Extra options for the request. The options must be in an
     *                                                    associative array, the key must be a valid CURL option name,
     *                                                    and the value the value for that key.
     * @param bool   $forceQueryStringFormattingForPost   If TRUE, and this is a POST request, the post data will be
     *                                                    formatted as a query string, instead of sending it as
     *                                                    multipart/form-data.
     * @param bool   $allowCustomPost                     If TRUE, and this is a POST request, then the custom post
     *                                                    fields will be set from the extra options.
     *
     * @return \YapepBase\Communication\CurlHttpWrapper
     *
     * @deprecated Will be removed in the next version, use SystemContainer::getCurlHttpRequest() instead.
     */
    public function get(
        $method,
        $url,
        $parameters = [],
        $additionalHeaders = [],
        $extraOptions = [],
        $forceQueryStringFormattingForPost = false,
        $allowCustomPost = false
    ) {
        return new CurlHttpWrapper(
            $method,
            $url,
            $parameters,
            $additionalHeaders,
            $extraOptions,
            $forceQueryStringFormattingForPost,
            $allowCustomPost
        );
    }
}
