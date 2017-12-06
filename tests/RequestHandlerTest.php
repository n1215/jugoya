<?php

namespace N1215\Jugoya;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\TextResponse;
use Zend\Diactoros\ServerRequest;

class RequestHandlerTest extends TestCase
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
        foreach(range(0, $middlewareCount - 1) as $index) {
            $middlewareText = 'middleware-' . $index;
            $middlewareStack[] = new FakeMiddleware($middlewareText);
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


        $app = new RequestHandler($coreHandler, $middlewareStack);

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

        $app = new RequestHandler($coreHandler, []);
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

        /** @var MiddlewareInterface $middleware */
        $middleware = \Mockery::mock(MiddlewareInterface::class);
        $middleware->shouldReceive('process')
            ->once()
            ->with($request, $coreHandler)
            ->andReturn($response);

        $app = new RequestHandler($coreHandler, [$middleware]);
        $result = $app->handle($request);

        $this->assertEquals($response, $result);
    }
}