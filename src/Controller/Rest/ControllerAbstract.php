<?php
declare(strict_types=1);

namespace YapepBase\Controller\Rest;

use YapepBase\Controller\Rest\Entity\RestError;
use YapepBase\Controller\Rest\Exception\Exception;
use YapepBase\Controller\Rest\Exception\ResourceDoesNotExistException;
use YapepBase\Exception\HttpException;
use YapepBase\Exception\RedirectException;
use YapepBase\Mime\MimeType;
use YapepBase\Request\Request;
use YapepBase\Response\Entity\Header;
use YapepBase\View\Data\SimpleData;
use YapepBase\View\Template\JsonTemplate;

/**
 * Base class for restful API controllers.
 */
abstract class ControllerAbstract extends \YapepBase\Controller\ControllerAbstract
{
    /** @var string|null */
    private $calledAction;

    public function run(string $action): void
    {
        $this->calledAction = $action;

        $validMethodsForCurrentAction = $this->getValidMethodsForAction();

        try {
            $this->response->setContentType(MimeType::JSON);

            $actionMethodName = $this->getActionPrefix() . $action;

            if (!method_exists($this, $actionMethodName)) {
                if ($this->request->getMethod() === Request::METHOD_HTTP_OPTIONS) {
                    $this->response->sendHeader(new Header('Allow', implode(', ', $validMethodsForCurrentAction)));

                    return;
                }

                throw new ResourceDoesNotExistException($this->request->getMethod(), $action);
            }

            parent::run($action);
        } catch (Exception $e) {
            $this->sendHeadersAccordingToError($e);
            $this->setErrorResponse($e);
        } catch (HttpException | RedirectException $e) {
            // This is a standard HTTP exception or a redirect exception, simply re-throw it
            throw $e;
        } catch (\Exception $e) {
            trigger_error(
                'Unhandled exception of ' . get_class($e) . ' while serving api response. Error: ' . $e->getMessage(),
                E_RECOVERABLE_ERROR
            );

            $this->response->setStatusCode(500);
            $this->setErrorResponse(new Exception());
        }
    }

    private function setErrorResponse(Exception $exception): void
    {
        $error    = new RestError($exception->getCodeString(), $exception->getMessage(), $exception->getRequestParams());
        $viewData = new SimpleData($error->toArray());
        $view     = new JsonTemplate($viewData);

        $this->response->setBody($view);
    }

    private function sendHeadersAccordingToError(Exception $e): void
    {
        $validMethodsForCurrentAction = $this->getValidMethodsForAction();

        $statusCode = $this->response->getStatusCode() < 400
            ? $e->getRecommendedHttpStatusCode()
            : $this->response->getStatusCode();

        $this->response->setStatusCode($statusCode);

        if ($statusCode === 401 && !$this->response->getOutputHandler()->hasHeader('WWW-Authenticate')) {
            $this->response->sendHeader(new Header('WWW-Authenticate', 'Session realm="Please provide the session token"'));
        } elseif ($statusCode === 405 && !$this->response->getOutputHandler()->hasHeader('Allow')) {
            $this->response->sendHeader(new Header('Allow', implode(', ', $validMethodsForCurrentAction)));
        }
    }

    protected function getActionPrefix(): string
    {
        return strtolower($this->request->getMethod());
    }

    private function getValidMethodsForAction(): array
    {
        $methods = [];
        foreach (get_class_methods($this) as $methodName) {
            if (preg_match('/^([a-z]+)' . preg_quote(ucfirst($this->getCalledAction()), '/') . '$/', $methodName, $matches)) {
                $methods[] = strtoupper($matches[1]);
            }
        }

        return $methods;
    }

    /**
     * @throws Exception
     */
    private function getCalledAction(): string
    {
        if (is_null($this->calledAction)) {
            throw new Exception('Action not called yet');
        }

        return $this->calledAction;
    }
}
