<?php

namespace N1215\Jugoya;

use Interop\Http\Server\RequestHandlerInterface;
use N1215\Jugoya\Resolver\MiddlewareResolverInterface;
use N1215\Jugoya\Resolver\RequestHandlerResolverInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\TextResponse;
use Zend\Diactoros\ServerRequest;

class LazyRequestHandlerTest extends TestCase
{
    public function testProcessWithMultiStack()
    {
        $request = new ServerRequest();

        $expectedContent = [];
        $expectedAttribute = [];

        $middlewareCount = 3;
        $middlewareStack = [];
        $middlewareRefs = [];
        foreach(range(0, $middlewareCount - 1) as $index) {
            $middlewareText = 'middleware-' . $index;
            $middlewareStack[] = new FakeMiddleware($middlewareText);
            $middlewareRefs[] = 'middlewareRef' . $index;
            array_unshift($expectedContent, $middlewareText);
            $expectedAttribute[] = $middlewareText;
        }

        $coreText = 'core';
        array_unshift($expectedContent, $coreText);


        /** @var RequestHandlerInterface|MockObject $coreHandler */
        $coreHandler = $this->createMock(RequestHandlerInterface::class);
        $coreHandler->expects($this->once())
            ->method('handle')
            ->with($this->callback(function (ServerRequestInterface $request) use ($expectedAttribute){
                // check Request modification by middleware
                $attribute = $request->getAttribute(FakeMiddleware::ATTRIBUTE_KEY);
                return $attribute === join(PHP_EOL, $expectedAttribute) . PHP_EOL;
            }))
            ->willReturn(new TextResponse($coreText));
        $coreHandlerRef = 'coreHandlerRef';

        /** @var RequestHandlerResolverInterface|MockObject $handlerResolver */
        $handlerResolver = $this->createMock(RequestHandlerResolverInterface::class);
        $handlerResolver->expects($this->once())
            ->method('resolve')
            ->with($coreHandlerRef)
            ->willReturn($coreHandler);

        /** @var MiddlewareResolverInterface|MockObject $middlewareResolver */
        $middlewareResolver = $this->createMock(MiddlewareResolverInterface::class);

        $map = array_map(function ($ref, $middleware) {
            return [$ref, $middleware];
        }, $middlewareRefs, $middlewareStack);

        $middlewareResolver->expects($this->exactly($middlewareCount))
            ->method('resolve')
            ->will($this->returnValueMap($map));

        $app = new LazyDelegateHandler($handlerResolver, $middlewareResolver, $coreHandlerRef, $middlewareRefs);

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
        $coreHandlerRef = 'coreHandlerRef';

        /** @var RequestHandlerResolverInterface|MockObject $handlerResolver */
        $handlerResolver = $this->createMock(RequestHandlerResolverInterface::class);
        $handlerResolver->expects($this->once())
            ->method('resolve')
            ->with($coreHandlerRef)
            ->willReturn($coreHandler);

        /** @var MiddlewareResolverInterface|MockObject $middlewareResolver */
        $middlewareResolver = $this->createMock(MiddlewareResolverInterface::class);

        $app = new LazyDelegateHandler($handlerResolver, $middlewareResolver, $coreHandlerRef, []);
        $result = $app->handle($request);

        $this->assertEquals($response, $result);
    }

    public function testProcessWithSingleStack()
    {
        $request = new ServerRequest();

        $middlewareText = 'middleware';
        $middleware = new FakeMiddleware($middlewareText);
        $middlewareRef = 'middlewareRef';

        $coreText = 'core';

        /** @var RequestHandlerInterface|MockObject $coreHandler */
        $coreHandler = $this->createMock(RequestHandlerInterface::class);
        $coreHandler->expects($this->once())
            ->method('handle')
            ->with($this->callback(function (ServerRequestInterface $request) use ($middlewareText){
                // check Request modification by middleware
                $attribute = $request->getAttribute(FakeMiddleware::ATTRIBUTE_KEY);
                return $attribute === $middlewareText . PHP_EOL;
            }))
            ->willReturn(new TextResponse($coreText));
        $coreHandlerRef = 'coreHandlerRef';

        /** @var RequestHandlerResolverInterface|MockObject $handlerResolver */
        $handlerResolver = $this->createMock(RequestHandlerResolverInterface::class);
        $handlerResolver->expects($this->once())
            ->method('resolve')
            ->with($coreHandlerRef)
            ->willReturn($coreHandler);

        /** @var MiddlewareResolverInterface|MockObject $middlewareResolver */
        $middlewareResolver = $this->createMock(MiddlewareResolverInterface::class);
        $middlewareResolver->expects($this->once())
            ->method('resolve')
            ->with($middlewareRef)
            ->willReturn($middleware);

        $app = new LazyDelegateHandler($handlerResolver, $middlewareResolver, $coreHandlerRef, [$middlewareRef]);

        $response = $app->handle($request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(join(PHP_EOL, [$coreText, $middlewareText]), $response->getBody()->__toString());
    }
}
