<?php

namespace N1215\Jugoya\Wrapper;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CallableHandlerTest extends TestCase
{

    public function testHandle()
    {
        $response = $this->createMock(ResponseInterface::class);
        $callable = function (ServerRequestInterface $request) use ($response) {
            $request->getBody();
            return $response;
        };

        $handler = new CallableHandler($callable);

        /** @var ServerRequestInterface|MockObject $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
            ->method('getBody');

        $result = $handler->handle($request);
        $this->assertSame($response, $result);
    }

    public function testHandleThrowsTypeError()
    {
        $callable = function(ServerRequestInterface $request) {
            return 'not a response';
        };

        $handler = new CallableHandler($callable);

        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);

        $this->expectException(\TypeError::class);

        $handler->handle($request);
    }
}