<?php

namespace N1215\Jugoya\Wrapper;

use Interop\Http\Server\RequestHandlerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CallableMiddlewareTest extends TestCase
{

    public function testProcess()
    {
        $callable = function(ServerRequestInterface $request, RequestHandlerInterface $handler) {
            return $handler->handle($request);
        };

        $middleware = new CallableMiddleware($callable);

        /** @var ServerRequestInterface|MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /** @var ResponseInterface|MockObject $response */
        $response = $this->createMock(ResponseInterface::class);
        /** @var RequestHandlerInterface|MockObject $handler */
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        $result = $middleware->process($request, $handler);

        $this->assertSame($response, $result);
    }

    public function testProcessThrowsTypeError()
    {
        $callable = function(ServerRequestInterface $request, RequestHandlerInterface $handler) {
            return 'not a response';
        };

        $middleware = new CallableMiddleware($callable);

        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        /** @var RequestHandlerInterface $handler */
        $handler = $this->createMock(RequestHandlerInterface::class);

        $this->expectException(\TypeError::class);
        $middleware->process($request, $handler);
    }
}
