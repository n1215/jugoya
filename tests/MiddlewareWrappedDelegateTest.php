<?php

namespace N1215\Jugoya;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MiddlewareWrappedDelegateTest extends TestCase
{

    protected function tearDown()
    {
        parent::tearDown();
        \Mockery::close();
    }

    public function testProcess()
    {
        /** @var DelegateInterface $delegate */
        $delegate = \Mockery::mock(DelegateInterface::class);
        /** @var ServerRequestInterface $request */
        $request = \Mockery::mock(ServerRequestInterface::class);
        /** @var ResponseInterface $response */
        $response = \Mockery::mock(ResponseInterface::class);
        /** @var MiddlewareInterface $middleware */
        $middleware = \Mockery::mock(MiddlewareInterface::class);
        $middleware->shouldReceive('process')
            ->once()
            ->with($request, $delegate)
            ->andReturn($response);

        $wrappedDelegate = new MiddlewareWrappedDelegate($delegate, $middleware);

        $result = $wrappedDelegate->process($request);
        $this->assertEquals($response, $result);
    }

}