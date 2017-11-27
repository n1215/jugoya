<?php

namespace N1215\Jugoya\Resolver;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;
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

        $callable = function(ServerRequestInterface $request, RequestHandlerInterface $handler) {
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

        $ref = 123456789;
        $resolver->resolve($ref);
    }

    /**
     * @expectedException \N1215\Jugoya\Resolver\UnresolvedException
     * @expectedExceptionCode  1
     */
    public function testResolveFailureWhenEntryNotFound()
    {
        $exception = new FakeNotFoundException('not found');

        /** @var ContainerInterface $container */
        $container = \Mockery::mock(ContainerInterface::class);
        $containerId = 'dummyId';
        $container->shouldReceive('get')
            ->once()
            ->with($containerId)
            ->andThrow($exception);

        $resolver = new MiddlewareResolver($container);
        $resolver->resolve($containerId);
    }

    /**
     * @expectedException \N1215\Jugoya\Resolver\UnresolvedException
     * @expectedExceptionCode 2
     */
    public function testResolveFailureWhenContainerException()
    {
        $exception = new FakeContainerException('container exception');

        /** @var ContainerInterface $container */
        $container = \Mockery::mock(ContainerInterface::class);
        $containerId = 'dummyId';
        $container->shouldReceive('get')
            ->once()
            ->with($containerId)
            ->andThrow($exception);

        $resolver = new MiddlewareResolver($container);
        $resolver->resolve($containerId);
    }

    /**
     * @expectedException \N1215\Jugoya\Resolver\UnresolvedException
     * @expectedExceptionCODE 3
     */
    public function testResolveFailureWhenTypeError()
    {
        /** @var ContainerInterface $container */
        $container = \Mockery::mock(ContainerInterface::class);
        $containerId = 'dummyId';
        $container->shouldReceive('get')
            ->once()
            ->with($containerId)
            ->andReturn(new \stdClass());

        $resolver = new MiddlewareResolver($container);
        $resolver->resolve($containerId);
    }
}