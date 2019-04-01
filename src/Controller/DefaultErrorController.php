<?php
declare(strict_types=1);

namespace YapepBase\Controller;

use YapepBase\View\Data\SimpleData;
use YapepBase\View\IRenderable;
use YapepBase\View\Template\SimpleHtmlTemplate;

/**
 * Default implementation for an error controller
 */
class DefaultErrorController extends ErrorControllerAbstract
{
    protected function do404(): IRenderable
    {
        return new SimpleHtmlTemplate(new SimpleData('<h1>404 - Page not found</h1>'));
    }

    protected function do500(): IRenderable
    {
        return new SimpleHtmlTemplate(new SimpleData('<h1>500 - Internal server error</h1>'));
    }
}
