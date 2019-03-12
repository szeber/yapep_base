<?php
declare(strict_types = 1);
/**
 * This file is part of YAPEPBase.
 *
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */
namespace YapepBase\Mime;

/**
 * This class contains the most commonly used MIME types as constants
 */
class MimeType
{
    /** HTML content type. */
    const HTML = 'text/html';

    /** XHTML content type. */
    const XHTML = 'application/xhtml+xml';

    /** CSS content type. */
    const CSS = 'text/css';

    /** Javascript content type. */
    const JAVASCRIPT = 'appliation/javascript';

    /** JSON content type. */
    const JSON = 'application/json';

    /** XML content type. */
    const XML = 'application/xml';

    /** Plain text content type. */
    const PLAINTEXT = 'text/plain';

    /** Octet stream content type. */
    const OCTET_STREAM = 'application/octet-stream';

    /** Image type: PNG. */
    const IMAGE_PNG = 'image/png';

    /** Image type: JPEG. */
    const IMAGE_JPEG = 'image/jpeg';

    /** Image type: GIF. */
    const IMAGE_GIF = 'image/gif';

    /** Video type: MP4. */
    const VIDEO_MP4 = 'video/mp4';

    /** Video type: WEBM. */
    const VIDEO_WEBM = 'video/webm';

    /** Video type: FLV. */
    const VIDEO_X_FLV = 'video/x-flv';
}
