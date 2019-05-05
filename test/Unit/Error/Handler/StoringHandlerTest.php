<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Error\Handler;

use Emul\Server\ServerData;
use Mockery\MockInterface;
use YapepBase\Error\Entity\Error;
use YapepBase\Error\Handler\Formatter\IFormatter;
use YapepBase\Error\Handler\StoringHandler;
use YapepBase\Request\IRequest;
use YapepBase\Request\Source\Params;
use YapepBase\Storage\IStorage;
use YapepBase\Test\Unit\TestAbstract;

class StoringHandlerTest extends TestAbstract
{
    /** @var IStorage|MockInterface */
    private $storage;
    /** @var IFormatter|MockInterface */
    private $formatter;
    /** @var string */
    private $errorId = 'id1';

    protected function setUp(): void
    {
        parent::setUp();

        $this->storage = \Mockery::mock(IStorage::class);
        $this->formatter = \Mockery::mock(IFormatter::class);
    }

    public function testHandleErrorWhenAlreadyStored_shouldDoNothing()
    {
        $handler = new StoringHandler($this->storage, $this->formatter);

        $this->expectGetFromStorage(1);
        $handler->handleError($this->getError());
    }

    public function testHandleError_shouldStoreDebugData()
    {
        $handler     = new StoringHandler($this->storage, $this->formatter);
        $debug       = 'Debug';
        $storedValue = \Mockery::pattern('#id1 \[E_ERROR\(1\)\]: message on line 2 in file#');

        $this->expectGetFromStorage(null);
        $this->expectFormat('Debug backtrace', \Mockery::any(), $debug);
        $this->expectSetToStorage($storedValue);
        $handler->handleError($this->getError());
    }

    public function testHandleErrorWhenRequestProvided_shouldStoreRequestAsWell()
    {
        $server  = new ServerData(['server' => 1]);
        $query   = new Params(['query' => 1]);
        $post    = new Params(['post' => 1]);
        $cookies = new Params(['cookies' => 1]);
        $env     = new Params(['env' => 1]);
        $request = $this->getRequest($server, $query, $post, $cookies, $env);

        $handler         = new StoringHandler($this->storage, $this->formatter, $request);
        $formattedDebug  = 'Debug';
        $formattedServer = 'serverData';
        $formattedGet    = 'getData';
        $formattedPost   = 'postData';
        $formattedCookie = 'cookieData';
        $formattedEnv    = 'envData';

        $this->expectGetFromStorage(null);
        $this->expectFormat('Debug backtrace', \Mockery::any(), $formattedDebug);
        $this->expectFormat('Server', $server->toArray(), $formattedServer);
        $this->expectFormat('Get', $query->toArray(), $formattedGet);
        $this->expectFormat('Post', $post->toArray(), $formattedPost);
        $this->expectFormat('Cookie', $cookies->toArray(), $formattedCookie);
        $this->expectFormat('Env', $env->toArray(), $formattedEnv);

        $this->expectRequestStored($formattedDebug, $formattedServer, $formattedGet, $formattedPost, $formattedCookie, $formattedEnv);
        $handler->handleError($this->getError());
    }

    private function getError(): Error
    {
        return new Error(1, 'message', 'file', 2, 'id1');
    }

    private function expectGetFromStorage($expectedResult): void
    {
        $this->storage
            ->shouldReceive('get')
            ->once()
            ->with($this->errorId)
            ->andReturn($expectedResult);
    }

    private function expectSetToStorage($expectedValue): void
    {
        $this->storage
            ->shouldReceive('set')
            ->once()
            ->with($this->errorId, $expectedValue);
    }

    private function expectRequestStored(string $debug, string $server, string $get, string $post, string $cookie, string $env)
    {
        $expectedValue = \Mockery::on(function ($value) use ($debug, $server, $get, $post, $cookie, $env) {
            $this->assertStringContainsString($debug, $value);
            $this->assertStringContainsString($server, $value);
            $this->assertStringContainsString($get, $value);
            $this->assertStringContainsString($post, $value);
            $this->assertStringContainsString($cookie, $value);
            $this->assertStringContainsString($env, $value);
            return true;
        });
        $this->expectSetToStorage($expectedValue);
    }

    private function expectFormat(string $sectionName, $expectedInput, $expectedResult): void
    {
        $this->formatter
            ->shouldReceive('format')
            ->once()
            ->with($sectionName, $expectedInput)
            ->andReturn($expectedResult);
    }

    private function getRequest($server, $query, $post, $cookies, $env): IRequest
    {
        return \Mockery::mock(IRequest::class)
            ->shouldReceive([
                'getServer'      => $server,
                'getQueryParams' => $query,
                'getPostParams'  => $post,
                'getCookies'     => $cookies,
                'getEnvParams'   => $env
            ])
            ->once()
            ->getMock();
    }
}
