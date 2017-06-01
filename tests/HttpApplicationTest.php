<?php

namespace N1215\Jugoya;

use Interop\Http\ServerMiddleware\DelegateInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HttpApplicationTest extends TestCase
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
        /** @var MiddlewareStack $stack */
        $stack = \Mockery::mock(MiddlewareStack::class);
        $stack->shouldReceive('process')
            ->once()
            ->with($request, $delegate)
            ->andReturn($response);

        $app = new HttpApplication($delegate, $stack);

        $result = $app->process($request);
        $this->assertEquals($response, $result);
    }

}