<?php

namespace N1215\Jugoya\Wrapper;

use Interop\Http\Server\RequestHandlerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CallableMiddlewareTest extends TestCase
{

    protected function tearDown()
    {
        parent::tearDown();
        \Mockery::close();
    }

    public function testProcess()
    {
        $callable = function(ServerRequestInterface $request, RequestHandlerInterface $handler) {
            return $handler->handle($request);
        };

        $middleware = new CallableMiddleware($callable);

        /** @var ServerRequestInterface $request */
        $request = \Mockery::mock(ServerRequestInterface::class);
        /** @var ResponseInterface $response */
        $response = \Mockery::mock(ResponseInterface::class);
        /** @var RequestHandlerInterface $handler */
        $handler = \Mockery::mock(RequestHandlerInterface::class);
        $handler->shouldReceive('handle')
            ->once()
            ->with($request)
            ->andReturn($response);

        $middleware->process($request, $handler);
    }


    /**
     * @expectedException \LogicException
     */
    public function testProcessThrowsException()
    {
        $callable = function(ServerRequestInterface $request, RequestHandlerInterface $handler) {
            return 'not a response';
        };

        $middleware = new CallableMiddleware($callable);

        /** @var ServerRequestInterface $request */
        $request = \Mockery::mock(ServerRequestInterface::class);
        /** @var RequestHandlerInterface $handler */
        $handler = \Mockery::mock(RequestHandlerInterface::class);
        $middleware->process($request, $handler);
    }
}