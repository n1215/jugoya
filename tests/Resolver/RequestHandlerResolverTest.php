<?php

namespace N1215\Jugoya\Resolver;

use Interop\Http\Server\RequestHandlerInterface;
use N1215\Jugoya\Wrapper\CallableHandler;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RequestHandlerResolverTest extends TestCase
{

    protected function tearDown()
    {
        parent::tearDown();
        \Mockery::close();
    }

    public function testResolveForHandlerInterface()
    {
        /** @var RequestHandlerInterface $handler */
        $handler = \Mockery::mock(RequestHandlerInterface::class);
        /** @var ContainerInterface $container */
        $container = \Mockery::mock(ContainerInterface::class);

        $resolver = new RequestHandlerResolver($container);
        $resolved = $resolver->resolve($handler);

        $this->assertEquals($handler, $resolved);
    }

    public function testResolveForCallable()
    {
        /** @var ContainerInterface $container */
        $container = \Mockery::mock(ContainerInterface::class);
        $resolver = new RequestHandlerResolver($container);

        $callable = function(ServerRequestInterface $request) {
            return \Mockery::mock(ResponseInterface::class);
        };

        $resolved = $resolver->resolve($callable);
        $this->assertInstanceOf(CallableHandler::class, $resolved);
    }

    public function testResolveByContainer()
    {
        /** @var RequestHandlerInterface $handler */
        $handler = \Mockery::mock(RequestHandlerInterface::class);
        $containerId = 'dummyId';

        /** @var ContainerInterface $container */
        $container = \Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('get')
            ->once()
            ->with($containerId)
            ->andReturn($handler);
        $resolver = new RequestHandlerResolver($container);

        $resolved = $resolver->resolve($containerId);

        $this->assertEquals($resolved, $handler);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testResolveFailure()
    {
        /** @var ContainerInterface $container */
        $container = \Mockery::mock(ContainerInterface::class);
        $resolver = new RequestHandlerResolver($container);

        $ref = 123456789;
        $resolver->resolve($ref);
    }

    /**
     * @expectedException \N1215\Jugoya\Resolver\UnresolvedException
     * @expectedExceptionCode 1
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

        $resolver = new RequestHandlerResolver($container);
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

        $resolver = new RequestHandlerResolver($container);
        $resolver->resolve($containerId);
    }

    /**
     * @expectedException \N1215\Jugoya\Resolver\UnresolvedException
     * @expectedExceptionCode 3
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

        $resolver = new RequestHandlerResolver($container);
        $resolver->resolve($containerId);
    }

}