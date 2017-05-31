<?php

namespace N1215\Jugoya\Resolver;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use N1215\Jugoya\Wrapper\CallableMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MiddlewareResolverTest extends TestCase
{

    protected function tearDown()
    {
        parent::tearDown();
        \Mockery::close();
    }

    public function testResolveForMiddlewareInterface()
    {
        /** @var MiddlewareInterface $middleware */
        $middleware = \Mockery::mock(MiddlewareInterface::class);
        /** @var ContainerInterface $container */
        $container = \Mockery::mock(ContainerInterface::class);

        $resolver = new MiddlewareResolver($container);
        $resolved = $resolver->resolve($middleware);

        $this->assertEquals($middleware, $resolved);
    }

    public function testResolveForCallable()
    {
        /** @var ContainerInterface $container */
        $container = \Mockery::mock(ContainerInterface::class);
        $resolver = new MiddlewareResolver($container);

        $callable = function(ServerRequestInterface $request, DelegateInterface $delegate) {
            return \Mockery::mock(ResponseInterface::class);
        };

        $resolved = $resolver->resolve($callable);
        $this->assertInstanceOf(CallableMiddleware::class, $resolved);
    }

    public function testResolveByContainer()
    {
        /** @var MiddlewareInterface $middleware */
        $middleware = \Mockery::mock(MiddlewareInterface::class);
        $containerId = 'dummyId';

        /** @var ContainerInterface $container */
        $container = \Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('get')
            ->once()
            ->with($containerId)
            ->andReturn($middleware);
        $resolver = new MiddlewareResolver($container);

        $resolved = $resolver->resolve($containerId);

        $this->assertEquals($resolved, $middleware);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testResolveFailure()
    {
        /** @var ContainerInterface $container */
        $container = \Mockery::mock(ContainerInterface::class);
        $resolver = new MiddlewareResolver($container);

        $entry = 123456789;
        $resolver->resolve($entry);
    }
}