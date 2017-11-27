<?php

namespace N1215\Jugoya\Wrapper;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CallableHandlerTest extends TestCase
{

    protected function tearDown()
    {
        parent::tearDown();
        \Mockery::close();
    }

    public function test__invoke()
    {
        $callable = function (ServerRequestInterface $request) {
            $request->getBody();
            return \Mockery::mock(ResponseInterface::class);
        };

        $handler = new CallableHandler($callable);

        /** @var ServerRequestInterface $request */
        $request = \Mockery::mock(ServerRequestInterface::class);
        $request->shouldReceive('getBody')->once();

        $handler->handle($request);
    }

    /**
     * @expectedException \LogicException
     */
    public function test__invokeThrowsException()
    {
        $callable = function(ServerRequestInterface $request) {
            return 'not a response';
        };

        $handler = new CallableHandler($callable);

        /** @var ServerRequestInterface $request */
        $request = \Mockery::mock(ServerRequestInterface::class);
        $handler->handle($request);
    }
}