<?php
declare(strict_types=1);
/**
 * This file is part of YAPEPBase.
 *
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */
namespace YapepBase\Response;

use YapepBase\View\ViewAbstract;

/**
 * Response interface
 */
interface IResponse
{
    /**
     * Constructor to set the output. If no output is given, it should use
     * whatever is default for the response.
     *
     * @param \YapepBase\Response\IOutput $output   The output handler to use.
     */
    public function __construct(IOutput $output = null);

    /**
     * Sets whether output buffering should be enabled or not.
     *
     * By default output buffering is enabled. If disabling this, the output will be echoed instead of using the
     * output object to send it, so the response object has no control over it. This may cause problems with the
     * sending of headers for example.
     * If disabling the buffering it will flush and disable all output buffers that were created after
     * the initialization of the response object. It will also send all headers that were added.
     *
     * @param bool $isEnabled   If TRUE, enables, if FALSE disables the output buffering.
     *
     * @return mixed
     *
     * @throws \YapepBase\Exception\ParameterException   If the output buffering is already in the specified status.
     */
    public function setOutputBufferingStatus($isEnabled);

    /**
     * Returns TRUE if the output buffering is enabled, FALSE if it's not.
     *
     * @return bool
     */
    public function getOutputBufferingStatus();

    /**
     * Sets the response body.
     *
     * @param \YapepBase\View\ViewAbstract $body   The response body
     *
     * @return void
     */
    public function setBody(ViewAbstract $body);

    /**
     * Sets the already rendered body.
     *
     * @param string $body   The response body.
     *
     * @return void
     */
    public function setRenderedBody($body);

    /**
     * Renders the output.
     *
     * @return void
     */
    public function render();

    /**
     * Sends the response
     *
     * @return void
     *
     * @throws \YapepBase\Exception\Exception   If called after sendError()
     */
    public function send();

    /**
     * Instantly outputs an error message.
     *
     * This method is used to signal a fatal error to the client.
     *
     * @return void
     *
     * @throws \YapepBase\Exception\Exception   If called after send()
     */
    public function sendError();

    /**
     * Clears all previous, not sent output in the buffer.
     *
     * @return void
     */
    public function clearAllOutput();
}
