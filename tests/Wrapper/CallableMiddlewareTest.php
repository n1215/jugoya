<?php

namespace N1215\Jugoya\Wrapper;

use Interop\Http\ServerMiddleware\DelegateInterface;
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
        $callable = function(ServerRequestInterface $request, DelegateInterface $delegate) {
            return $delegate->process($request);
        };

        $middleware = new CallableMiddleware($callable);

        /** @var ServerRequestInterface $request */
        $request = \Mockery::mock(ServerRequestInterface::class);
        /** @var ResponseInterface $response */
        $response = \Mockery::mock(ResponseInterface::class);
        /** @var DelegateInterface $delegate */
        $delegate = \Mockery::mock(DelegateInterface::class);
        $delegate->shouldReceive('process')
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
        $callable = function(ServerRequestInterface $request, DelegateInterface $delegate) {
            return 'not a response';
        };

        $middleware = new CallableMiddleware($callable);

        /** @var ServerRequestInterface $request */
        $request = \Mockery::mock(ServerRequestInterface::class);
        /** @var DelegateInterface $delegate */
        $delegate = \Mockery::mock(DelegateInterface::class);
        $middleware->process($request, $delegate);
    }
}