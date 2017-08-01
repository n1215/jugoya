<?php

namespace N1215\Jugoya\Wrapper;

use N1215\Jugoya\HandlerInterface;
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
        $callable = function(ServerRequestInterface $request, HandlerInterface $delegate) {
            return $delegate->__invoke($request);
        };

        $middleware = new CallableMiddleware($callable);

        /** @var ServerRequestInterface $request */
        $request = \Mockery::mock(ServerRequestInterface::class);
        /** @var ResponseInterface $response */
        $response = \Mockery::mock(ResponseInterface::class);
        /** @var HandlerInterface $delegate */
        $delegate = \Mockery::mock(HandlerInterface::class);
        $delegate->shouldReceive('__invoke')
            ->once()
            ->with($request)
            ->andReturn($response);

        $middleware->process($request, $delegate);
    }


    /**
     * @expectedException \LogicException
     */
    public function testProcessThrowsException()
    {
        $callable = function(ServerRequestInterface $request, HandlerInterface $delegate) {
            return 'not a response';
        };

        $middleware = new CallableMiddleware($callable);

        /** @var ServerRequestInterface $request */
        $request = \Mockery::mock(ServerRequestInterface::class);
        /** @var HandlerInterface $delegate */
        $delegate = \Mockery::mock(HandlerInterface::class);
        $middleware->process($request, $delegate);
    }
}