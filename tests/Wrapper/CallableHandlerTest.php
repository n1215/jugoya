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
        $callable = function(ServerRequestInterface $request) {
            $request->getBody();
            return \Mockery::mock(ResponseInterface::class);
        };

        $delegate = new CallableHandler($callable);

        /** @var ServerRequestInterface $request */
        $request = \Mockery::mock(ServerRequestInterface::class);
        $request->shouldReceive('getBody')->once();

        $delegate->__invoke($request);
    }

    /**
     * @expectedException \LogicException
     */
    public function test__invokeThrowsException()
    {
        $callable = function(ServerRequestInterface $request) {
            return 'not a response';
        };

        $delegate = new CallableHandler($callable);

        /** @var ServerRequestInterface $request */
        $request = \Mockery::mock(ServerRequestInterface::class);
        $delegate->__invoke($request);
    }
}