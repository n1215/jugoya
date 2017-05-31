<?php

namespace N1215\Jugoya\Wrapper;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CallableDelegateTest extends TestCase
{

    protected function tearDown()
    {
        parent::tearDown();
        \Mockery::close();
    }

    public function testProcess()
    {
        $callable = function(ServerRequestInterface $request) {
            $request->getBody();
            return \Mockery::mock(ResponseInterface::class);
        };

        $delegate = new CallableDelegate($callable);

        /** @var ServerRequestInterface $request */
        $request = \Mockery::mock(ServerRequestInterface::class);
        $request->shouldReceive('getBody')->once();

        $delegate->process($request);
    }

    /**
     * @expectedException \LogicException
     */
    public function testProcessThrowsException()
    {
        $callable = function(ServerRequestInterface $request) {
            return 'not a response';
        };

        $delegate = new CallableDelegate($callable);

        /** @var ServerRequestInterface $request */
        $request = \Mockery::mock(ServerRequestInterface::class);
        $delegate->process($request);
    }
}