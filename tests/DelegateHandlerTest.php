<?php

namespace N1215\Jugoya;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\TextResponse;
use Laminas\Diactoros\ServerRequest;

class DelegateHandlerTest extends TestCase
{
    public function testProcessWithMultiStack()
    {
        $request = new ServerRequest();

        $expectedContent = [];
        $expectedAttribute = [];

        $middlewareCount = 20;
        $middlewareStack = [];
        foreach(range(0, $middlewareCount - 1) as $index) {
            $middlewareText = 'middleware-' . $index;
            $middlewareStack[] = new FakeMiddleware($middlewareText);
            array_unshift($expectedContent, $middlewareText);
            $expectedAttribute[] = $middlewareText;
        }

        $coreText = 'core';
        array_unshift($expectedContent, $coreText);

        /** @var RequestHandlerInterface|MockObject $coreHandler */
        $coreHandler = $this->createMock(RequestHandlerInterface::class);
        $coreHandler->expects($this->atLeastOnce())->method('handle')
            ->with($this->callback(function (ServerRequestInterface $request) use ($expectedAttribute){
                // check Request modification by middleware
                $attribute = $request->getAttribute(FakeMiddleware::ATTRIBUTE_KEY);
                return $attribute === join(PHP_EOL, $expectedAttribute) . PHP_EOL;
            }))
            ->willReturn(new TextResponse($coreText));

        $app = new DelegateHandler($coreHandler, $middlewareStack);

        $response = $app->handle($request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(join(PHP_EOL, $expectedContent), $response->getBody()->__toString());
    }

    public function testProcessWithEmptyStack()
    {
        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);

        /** @var ResponseInterface $response */
        $response = $this->createMock(ResponseInterface::class);

        /** @var RequestHandlerInterface|MockObject $coreHandler */
        $coreHandler = $this->createMock(RequestHandlerInterface::class);
        $coreHandler->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        $app = new DelegateHandler($coreHandler, []);
        $result = $app->handle($request);

        $this->assertEquals($response, $result);
    }

    public function testProcessWithSingleStack()
    {
        /** @var ServerRequestInterface $request */
        $request = $this->createMock(ServerRequestInterface::class);

        /** @var ResponseInterface $response */
        $response = $this->createMock(ResponseInterface::class);

        /** @var RequestHandlerInterface $coreHandler */
        $coreHandler = $this->createMock(RequestHandlerInterface::class);

        /** @var MiddlewareInterface|MockObject $middleware */
        $middleware = $this->createMock(MiddlewareInterface::class);
        $middleware->expects($this->once())
            ->method('process')
            ->with($request, $this->isInstanceOf(DelegateHandler::class))
            ->willReturn($response);

        $app = new DelegateHandler($coreHandler, [$middleware]);
        $result = $app->handle($request);

        $this->assertEquals($response, $result);
    }
}
