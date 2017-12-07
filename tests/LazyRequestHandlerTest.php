<?php

namespace N1215\Jugoya;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;
use Mockery\MockInterface;
use N1215\Jugoya\Resolver\MiddlewareResolverInterface;
use N1215\Jugoya\Resolver\RequestHandlerResolverInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\TextResponse;
use Zend\Diactoros\ServerRequest;

class LazyRequestHandlerTest extends TestCase
{

    protected function tearDown()
    {
        parent::tearDown();
        \Mockery::close();
    }

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


        /** @var RequestHandlerInterface $coreHandler */
        $coreHandler = \Mockery::mock(RequestHandlerInterface::class);
        $coreHandler->shouldReceive('handle')
            ->with(\Mockery::on(function (ServerRequestInterface $request) use ($expectedAttribute){
                // check Request modification by middleware
                $attribute = $request->getAttribute(FakeMiddleware::ATTRIBUTE_KEY);
                return $attribute === join(PHP_EOL, $expectedAttribute) . PHP_EOL;
            }))
            ->andReturn(new TextResponse($coreText));
        $coreHandlerRef = 'coreHandlerRef';

        /** @var RequestHandlerResolverInterface|MockInterface $handlerResolver */
        $handlerResolver = \Mockery::mock(RequestHandlerResolverInterface::class);
        $handlerResolver->shouldReceive('resolve')
            ->with($coreHandlerRef)
            ->once()
            ->andReturn($coreHandler);

        $handlerResolver->shouldReceive('resolve')
            ->with($coreHandler)
            ->andReturn($coreHandler);

        /** @var MiddlewareResolverInterface|MockInterface $middlewareResolver */
        $middlewareResolver = \Mockery::mock(MiddlewareResolverInterface::class);
        foreach(range(0, $middlewareCount - 1) as $index) {
            $middlewareResolver->shouldReceive('resolve')
                ->once()
                ->with($middlewareRefs[$index])
                ->andReturn($middlewareStack[$index]);
        }

        $app = new LazyRequestHandler($handlerResolver, $middlewareResolver, $coreHandlerRef, $middlewareRefs);

        $response = $app->handle($request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(join(PHP_EOL, $expectedContent), $response->getBody()->__toString());
    }

    public function testProcessWithEmptyStack()
    {
        /** @var ServerRequestInterface $request */
        $request = \Mockery::mock(ServerRequestInterface::class);

        /** @var ResponseInterface $response */
        $response = \Mockery::mock(ResponseInterface::class);

        /** @var RequestHandlerInterface $coreHandler */
        $coreHandler = \Mockery::mock(RequestHandlerInterface::class);
        $coreHandler->shouldReceive('handle')
            ->once()
            ->with($request)
            ->andReturn($response);
        $coreHandlerRef = 'coreHandlerRef';

        /** @var RequestHandlerResolverInterface|MockInterface $handlerResolver */
        $handlerResolver = \Mockery::mock(RequestHandlerResolverInterface::class);
        $handlerResolver->shouldReceive('resolve')
            ->once()
            ->with($coreHandlerRef)
            ->andReturn($coreHandler);

        /** @var MiddlewareResolverInterface|MockInterface $middlewareResolver */
        $middlewareResolver = \Mockery::mock(MiddlewareResolverInterface::class);

        $app = new LazyRequestHandler($handlerResolver, $middlewareResolver, $coreHandlerRef, []);
        $result = $app->handle($request);

        $this->assertEquals($response, $result);
    }

    public function testProcessWithSingleStack()
    {
        /** @var ServerRequestInterface $request */
        $request = \Mockery::mock(ServerRequestInterface::class);

        /** @var ResponseInterface $response */
        $response = \Mockery::mock(ResponseInterface::class);

        /** @var RequestHandlerInterface $coreHandler */
        $coreHandler = \Mockery::mock(RequestHandlerInterface::class);
        $coreHandlerRef = 'coreHandlerRef';

        /** @var RequestHandlerResolverInterface|MockInterface $handlerResolver */
        $handlerResolver = \Mockery::mock(RequestHandlerResolverInterface::class);
        $handlerResolver->shouldReceive('resolve')
            ->with($coreHandlerRef)
            ->once()
            ->andReturn($coreHandler);

        /** @var MiddlewareInterface $middleware */
        $middleware = \Mockery::mock(MiddlewareInterface::class);
        $middleware->shouldReceive('process')
            ->once()
            ->with($request, $coreHandler)
            ->andReturn($response);
        $middlewareRef = 'middlewareRef';

        /** @var MiddlewareResolverInterface|MockInterface $middlewareResolver */
        $middlewareResolver = \Mockery::mock(MiddlewareResolverInterface::class);
        $middlewareResolver->shouldReceive('resolve')
            ->once()
            ->with($middlewareRef)
            ->andReturn($middleware);

        $app = new LazyRequestHandler($handlerResolver, $middlewareResolver, $coreHandlerRef, [$middlewareRef]);
        $result = $app->handle($request);

        $this->assertEquals($response, $result);
    }
}