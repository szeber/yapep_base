<?php
declare(strict_types = 1);

namespace YapepBase\Test\Unit\Debug\Item;

use YapepBase\Debug\Item\CurlRequest;

class CurlRequestTest extends TestAbstract
{

    public function testConstructor_shouldStoreGivenValues()
    {
        $protocol      = 'http';
        $requestMethod = 'GET';
        $url           = 'test.dev';
        $parameters    = ['param' => 1];
        $headers       = ['header' => 1];
        $options       = ['option' => 1];

        $this->expectGetCurrentTime();
        $curlRequest = new CurlRequest($this->dateHelper, $protocol, $requestMethod, $url, $parameters, $headers, $options);

        $this->assertSame($protocol, $curlRequest->getProtocol());
        $this->assertSame($requestMethod, $curlRequest->getRequestMethod());
        $this->assertSame($url, $curlRequest->getUrl());
        $this->assertSame($parameters, $curlRequest->getParameters());
        $this->assertSame($headers, $curlRequest->getHeaders());
        $this->assertSame($options, $curlRequest->getOptions());
        $this->assertSame($this->currentTime, $curlRequest->getStartTime());
    }
}
