<?php

namespace N1215\Jugoya\Wrapper;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CallableHandlerTest extends TestCase
{

    public function test__invoke()
    {
        $response = $this->createMock(ResponseInterface::class);
        $callable = function (ServerRequestInterface $request) use ($response) {
            $request->getBody();
            return $response;
        };

        $handler = new CallableHandler($callable);

        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
            ->method('getBody');

        $result = $handler->handle($request);
        $this->assertSame($response, $result);
    }

    public function test__invokeThrowsException()
    {
        $callable = function(ServerRequestInterface $request) {
            return 'not a response';
        };

        $handler = new CallableHandler($callable);

        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);

        $this->expectException(\LogicException::class);

        $handler->handle($request);
    }
}