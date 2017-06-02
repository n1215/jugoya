<?php

namespace N1215\Jugoya;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\TextResponse;
use Zend\Diactoros\ServerRequest;

class HttpApplicationTest extends TestCase
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

        $delegateText = 'delegate';
        array_unshift($expectedContent, $delegateText);


        /** @var DelegateInterface $delegate */
        $delegate = \Mockery::mock(DelegateInterface::class);
        $delegate->shouldReceive('process')
            ->with(\Mockery::on(function (ServerRequestInterface $request) use ($expectedAttribute){
                // check Request modification by middleware
                $attribute = $request->getAttribute(FakeMiddleware::ATTRIBUTE_KEY);
                return $attribute === join(PHP_EOL, $expectedAttribute) . PHP_EOL;
            }))
            ->andReturn(new TextResponse($delegateText));


        $app = new HttpApplication($delegate, $middlewareStack);

        $response = $app->process($request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(join(PHP_EOL, $expectedContent), $response->getBody()->__toString());
    }

    public function testProcessWithEmptyStack()
    {
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

        $app = new HttpApplication($delegate, []);
        $result = $app->process($request);

        $this->assertEquals($response, $result);
    }

    public function testProcessWithSingleStack()
    {
        /** @var ServerRequestInterface $request */
        $request = \Mockery::mock(ServerRequestInterface::class);

        /** @var ResponseInterface $response */
        $response = \Mockery::mock(ResponseInterface::class);

        /** @var DelegateInterface $delegate */
        $delegate = \Mockery::mock(DelegateInterface::class);

        /** @var MiddlewareInterface $middleware */
        $middleware = \Mockery::mock(MiddlewareInterface::class);
        $middleware->shouldReceive('process')
            ->once()
            ->with($request, $delegate)
            ->andReturn($response);

        $app = new HttpApplication($delegate, [$middleware]);
        $result = $app->process($request);

        $this->assertEquals($response, $result);
    }
}