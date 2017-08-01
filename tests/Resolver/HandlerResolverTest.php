<?php

namespace N1215\Jugoya\Resolver;

use N1215\Jugoya\HandlerInterface;
use N1215\Jugoya\Wrapper\CallableHandler;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HandlerResolverTest extends TestCase
{

    protected function tearDown()
    {
        parent::tearDown();
        \Mockery::close();
    }

    public function testResolveForHandlerInterface()
    {
        /** @var HandlerInterface $handler */
        $handler = \Mockery::mock(HandlerInterface::class);
        /** @var ContainerInterface $container */
        $container = \Mockery::mock(ContainerInterface::class);

        $resolver = new HandlerResolver($container);
        $resolved = $resolver->resolve($handler);

        $this->assertEquals($handler, $resolved);
    }

    public function testResolveForCallable()
    {
        /** @var ContainerInterface $container */
        $container = \Mockery::mock(ContainerInterface::class);
        $resolver = new HandlerResolver($container);

        $callable = function(ServerRequestInterface $request) {
            return \Mockery::mock(ResponseInterface::class);
        };

        $resolved = $resolver->resolve($callable);
        $this->assertInstanceOf(CallableHandler::class, $resolved);
    }

    public function testResolveByContainer()
    {
        /** @var HandlerInterface $handler */
        $handler = \Mockery::mock(HandlerInterface::class);
        $containerId = 'dummyId';

        /** @var ContainerInterface $container */
        $container = \Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('get')
            ->once()
            ->with($containerId)
            ->andReturn($handler);
        $resolver = new HandlerResolver($container);

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
        $resolver = new HandlerResolver($container);

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

        $resolver = new HandlerResolver($container);
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

        $resolver = new HandlerResolver($container);
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

        $resolver = new HandlerResolver($container);
        $resolver->resolve($containerId);
    }

}