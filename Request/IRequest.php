<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Request
 * @author       Zsolt Szeberenyi <szeber@yapep.org>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */


namespace YapepBase\Request;

/**
 * Router interface
 *
 * @package    YapepBase
 * @subpackage Request
 */
interface IRequest {

    /** CLI method. Used by CLI requests. */
    const METHOD_CLI = 'CLI';
    /** GET HTTP method. */
    const METHOD_HTTP_GET = 'GET';
    /** POST HTTP method. */
    const METHOD_HTTP_POST = 'POST';
    /** PUT HTTP method. */
    const METHOD_HTTP_PUT = 'PUT';
    /** HEAD HTTP method. */
    const METHOD_HTTP_HEAD = 'HEAD';
    /** OPTIONS HTTP method. */
    const METHOD_HTTP_OPTIONS = 'OPTIONS';
    /** DELETE HTTP method. */
    const METHOD_HTTP_DELETE = 'DELETE';

    /**
     * Returns the target of the request.
     *
     * @return string   The target of the request.
     */
    public function getTarget();

    /**
     * Returns the method of the request
     *
     * @return string   {@uses self::METHOD_*}
     */
    public function getMethod();
}