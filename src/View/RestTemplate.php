<?php
declare(strict_types = 1);
/**
 * This file is part of YAPEPBase.
 *
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */
namespace YapepBase\View;

use YapepBase\Exception\ViewException;
use YapepBase\Mime\MimeType;

/**
 * RestTemplate class
 */
class RestTemplate extends ViewAbstract
{
    /**
     * The view's content.
     *
     * @var array
     */
    protected $content;

    /**
     * The root node's name for XML format output.
     *
     * @var string
     */
    protected $rootNodeName = 'xml';

    /**
     * Sets the view's content
     *
     * @param array $content   The content
     *
     * @return void
     */
    public function setContent(array $content)
    {
        $this->content = $content;
    }

    /**
     * Sets the root node name for XML format output.
     *
     * @param string $nodeName   The node name
     *
     * @return void
     */
    public function setRootNode($nodeName)
    {
        $this->rootNodeName = (string)$nodeName;
    }

    /**
     * Renders the view and returns it.
     *
     * @return void
     */
    public function render()
    {
        echo $this->renderContent();
    }

    /**
     * Renders the content, and returns it.
     *
     * @return string
     *
     * @throws \YapepBase\Exception\ViewException
     */
    protected function renderContent()
    {
        switch ($this->contentType) {
            case MimeType::XML:
                return $this->getXmlFromData([$this->rootNodeName => $this->content]);
                break;

            case MimeType::JAVASCRIPT:
            case MimeType::JSON:
                return \json_encode($this->content);
                break;

            default:
                throw new ViewException('Unknown content type for RestView: ' . $this->contentType);
                break;
        }
    }

    /**
     * Returns the data as XML.
     *
     * For a well formed XML, the data has to already be escaped.
     *
     * @param mixed $data   The data.
     *
     * @return string
     */
    protected function getXmlFromData($data)
    {
        if (is_array($data) || (($data instanceof \Iterator) && ($data instanceof \ArrayAccess))) {
            $xml = '';
            foreach ($data as $key => $value) {
                $xml .= '<' . $key . '>' . $this->getXmlFromData($value) . '</' . $key . '>';
            }

            return $xml;
        } else {
            return (string)$data;
        }
    }
}
