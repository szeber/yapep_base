<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Request;

use Emul\Server\ServerData;
use YapepBase\Helper\ArrayHelper;
use YapepBase\Request\Request;
use YapepBase\Request\Source\Files;
use YapepBase\Request\Source\IFiles;
use YapepBase\Request\Source\ISource;
use YapepBase\Request\Source\Params;
use YapepBase\Test\Unit\TestAbstract;

class HttpRequestTest extends TestAbstract
{
    /** @var ISource */
    protected $queryParams;
    /** @var ISource */
    protected $postParams;
    /** @var ISource */
    protected $cookies;
    /** @var ISource */
    protected $envParams;
    /** @var ISource */
    protected $inputParams;
    /** @var IFiles */
    protected $files;
    /** @var ServerData */
    protected $server;

    /** @var Request */
    protected $httpRequest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pimpleContainer[ArrayHelper::class] = new ArrayHelper();
    }

    protected function getHttpRequest(
        array $queryParams = [],
        array $postParams = [],
        array $server = []
    ): Request {
        $this->queryParams = new Params($queryParams);
        $this->postParams  = new Params($postParams);
        $this->cookies     = new Params([]);
        $this->envParams   = new Params([]);
        $this->inputParams = new Params([]);
        $this->files       = new Files([]);
        $this->server      = new ServerData($server);

        return new Request(
            $this->queryParams,
            $this->postParams,
            $this->cookies,
            $this->envParams,
            $this->inputParams,
            $this->files,
            $this->server
        );
    }

    public function testConstruct_shouldSetQueryParams()
    {
        $queryParams = $this->getHttpRequest()->getQueryParams();
        $this->assertSame($this->queryParams, $queryParams);
    }

    public function testConstruct_shouldSetPostParams()
    {
        $postParams = $this->getHttpRequest()->getPostParams();
        $this->assertSame($this->postParams, $postParams);
    }

    public function testConstruct_shouldSetCookies()
    {
        $cookies = $this->getHttpRequest()->getCookies();
        $this->assertSame($this->cookies, $cookies);
    }

    public function testConstruct_shouldSetEnvParams()
    {
        $envParams = $this->getHttpRequest()->getEnvParams();
        $this->assertSame($this->envParams, $envParams);
    }

    public function testConstruct_shouldSetInputParams()
    {
        $inputParams = $this->getHttpRequest()->getInputParams();
        $this->assertSame($this->inputParams, $inputParams);
    }

    public function testConstruct_shouldSetFiles()
    {
        $files = $this->getHttpRequest()->getFiles();
        $this->assertSame($this->files, $files);
    }

    public function testConstruct_shouldSetServer()
    {
        $server = $this->getHttpRequest()->getServer();
        $this->assertSame($this->server, $server);
    }

    public function testGetParamAsIntWhenParamIsInCustomAndQueryAndPost_shouldReturnCustomFirst()
    {
        $param       = 'test';
        $queryParams = [$param => 2];
        $postParams  = [$param => 3];
        $httpRequest = $this->getHttpRequest($queryParams, $postParams);
        $httpRequest->getCustomParams()->set($param, 1);

        $result = $httpRequest->getParamAsInt($param);

        $this->assertSame(1, $result);
    }

    public function testGetParamAsIntWhenParamIsInQueryAndPost_shouldReturnQueryFirst()
    {
        $param       = 'test';
        $queryParams = [$param => 2];
        $postParams  = [$param => 3];
        $httpRequest = $this->getHttpRequest($queryParams, $postParams);

        $result = $httpRequest->getParamAsInt($param);

        $this->assertSame(2, $result);
    }

    public function testGetParamAsIntWhenParamIsInPostOnly_shouldReturnPost()
    {
        $param       = 'test';
        $postParams  = [$param => 3];
        $httpRequest = $this->getHttpRequest([], $postParams);

        $result = $httpRequest->getParamAsInt($param);

        $this->assertSame(3, $result);
    }

    public function testGetParamAsIntWhenParamNotSent_shouldReturnDefault()
    {
        $httpRequest = $this->getHttpRequest([], []);

        $result = $httpRequest->getParamAsInt('nonExistent', 13);

        $this->assertSame(13, $result);
    }

    public function testGetParamAsStringWhenParamIsInQueryAndPost_shouldReturnQueryFirst()
    {
        $param       = 'test';
        $queryParams = [$param => 2];
        $postParams  = [$param => 3];
        $httpRequest = $this->getHttpRequest($queryParams, $postParams);

        $result = $httpRequest->getParamAsString($param);

        $this->assertSame('2', $result);
    }

    public function testGetParamAsFloatWhenParamIsInQueryAndPost_shouldReturnQueryFirst()
    {
        $param       = 'test';
        $queryParams = [$param => 2];
        $postParams  = [$param => 3];
        $httpRequest = $this->getHttpRequest($queryParams, $postParams);

        $result = $httpRequest->getParamAsFloat($param);

        $this->assertSame(2.0, $result);
    }

    public function testGetParamAsArrayWhenParamIsInQueryAndPost_shouldReturnQueryFirst()
    {
        $param       = 'test';
        $queryParams = [$param => 2];
        $postParams  = [$param => 3];
        $httpRequest = $this->getHttpRequest($queryParams, $postParams);

        $result = $httpRequest->getParamAsArray($param);

        $this->assertSame([2], $result);
    }

    public function testGetParamAsBoolWhenParamIsInQueryAndPost_shouldReturnQueryFirst()
    {
        $param       = 'test';
        $queryParams = [$param => false];
        $postParams  = [$param => true];
        $httpRequest = $this->getHttpRequest($queryParams, $postParams);

        $result = $httpRequest->getParamAsBool($param);

        $this->assertSame(false, $result);
    }

    public function testGetTarget_shouldReturnUrlPath()
    {
        $requestUri = '/test/whatever?param=1';
        $server     = ['REQUEST_URI' => $requestUri];
        $target     = $this->getHttpRequest([], [], $server)->getTargetUri();

        $expectedTarget = '/test/whatever';

        $this->assertSame($expectedTarget, $target);
    }

    public function testGetMethod_shouldReturnRequestMethod()
    {
        $requestMethod = 'method';
        $server        = ['REQUEST_METHOD' => $requestMethod];
        $method        = $this->getHttpRequest([], [], $server)->getMethod();

        $this->assertSame($requestMethod, $method);
    }

    public function protocolProvider()
    {
        return [
            'https' => ['On', Request::PROTOCOL_HTTPS],
            'http'  => ['',   Request::PROTOCOL_HTTP],
        ];
    }

    /**
     * @dataProvider protocolProvider
     */
    public function testGetProtocol_shouldReturnTrueIfHttps(string $https, string $expectedResult)
    {
        $server   = ['HTTPS' => $https];
        $protocol = $this->getHttpRequest([], [], $server)->getProtocol();

        $this->assertSame($expectedResult, $protocol);
    }
}
