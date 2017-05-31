<?php

namespace N1215\Jugoya\Resolver;

use Interop\Http\ServerMiddleware\DelegateInterface;
use N1215\Jugoya\Wrapper\CallableDelegate;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DelegateResolverTest extends TestCase
{

    protected function tearDown()
    {
        parent::tearDown();
        \Mockery::close();
    }

    public function testResolveForDelegateInterface()
    {
        /** @var DelegateInterface $middleware */
        $middleware = \Mockery::mock(DelegateInterface::class);
        /** @var ContainerInterface $container */
        $container = \Mockery::mock(ContainerInterface::class);

        $resolver = new DelegateResolver($container);
        $resolved = $resolver->resolve($middleware);

        $this->assertEquals($middleware, $resolved);
    }

    public function testResolveForCallable()
    {
        /** @var ContainerInterface $container */
        $container = \Mockery::mock(ContainerInterface::class);
        $resolver = new DelegateResolver($container);

        $callable = function(ServerRequestInterface $request) {
            return \Mockery::mock(ResponseInterface::class);
        };

        $resolved = $resolver->resolve($callable);
        $this->assertInstanceOf(CallableDelegate::class, $resolved);
    }

    public function testResolveByContainer()
    {
        /** @var DelegateInterface $ */
        $delegate = \Mockery::mock(DelegateInterface::class);
        $containerId = 'dummyId';

        /** @var ContainerInterface $container */
        $container = \Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('get')
            ->once()
            ->with($containerId)
            ->andReturn($delegate);
        $resolver = new DelegateResolver($container);

        $resolved = $resolver->resolve($containerId);

        $this->assertEquals($resolved, $delegate);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testResolveFailure()
    {
        /** @var ContainerInterface $container */
        $container = \Mockery::mock(ContainerInterface::class);
        $resolver = new DelegateResolver($container);

        $entry = 123456789;
        $resolver->resolve($entry);
    }
}