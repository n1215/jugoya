<?php

namespace N1215\Jugoya\Resolver;

use Psr\Http\Server\RequestHandlerInterface;
use N1215\Jugoya\Wrapper\CallableHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RequestHandlerResolverTest extends TestCase
{
    public function testResolveForHandlerInterface()
    {
        /** @var RequestHandlerInterface $handler */
        $handler = $this->createMock(RequestHandlerInterface::class);
        /** @var ContainerInterface $container */
        $container = $this->createMock(ContainerInterface::class);

        $resolver = new RequestHandlerResolver($container);
        $resolved = $resolver->resolve($handler);

        $this->assertEquals($handler, $resolved);
    }

    public function testResolveForCallable()
    {
        /** @var ContainerInterface $container */
        $container = $this->createMock(ContainerInterface::class);
        $resolver = new RequestHandlerResolver($container);

        $callable = function(ServerRequestInterface $request) {
            return $this->createMock(ResponseInterface::class);
        };

        $resolved = $resolver->resolve($callable);
        $this->assertInstanceOf(CallableHandler::class, $resolved);
    }

    public function testResolveByContainer()
    {
        /** @var RequestHandlerInterface $handler */
        $handler = $this->createMock(RequestHandlerInterface::class);
        $containerId = 'dummyId';

        /** @var ContainerInterface|MockObject $container */
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
            ->method('get')
            ->with($containerId)
            ->willReturn($handler);
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
        $container = $this->createMock(ContainerInterface::class);
        $resolver = new RequestHandlerResolver($container);

        $ref = 123456789;
        $resolver->resolve($ref);
    }

    public function testResolveFailureWhenEntryNotFound()
    {
        $exception = new FakeNotFoundException('not found');

        /** @var ContainerInterface|MockObject $container */
        $container = $this->createMock(ContainerInterface::class);
        $containerId = 'dummyId';
        $container->expects($this->once())
            ->method('get')
            ->with($containerId)
            ->willThrowException($exception);

        $resolver = new RequestHandlerResolver($container);
        $this->expectExceptionCode(UnresolvedException::class);
        $this->expectExceptionCode(1);

        $resolver->resolve($containerId);
    }

    public function testResolveFailureWhenContainerException()
    {
        $exception = new FakeContainerException('container exception');

        /** @var ContainerInterface|MockObject $container */
        $container = $this->createMock(ContainerInterface::class);
        $containerId = 'dummyId';
        $container->expects($this->once())
            ->method('get')
            ->with($containerId)
            ->willThrowException($exception);

        $resolver = new RequestHandlerResolver($container);
        $this->expectExceptionCode(UnresolvedException::class);
        $this->expectExceptionCode(2);

        $resolver->resolve($containerId);
    }

    public function testResolveFailureWhenTypeError()
    {
        /** @var ContainerInterface|MockObject $container */
        $container = $this->createMock(ContainerInterface::class);
        $containerId = 'dummyId';
        $container->expects($this->once())
            ->method('get')
            ->with($containerId)
            ->willReturn(new \stdClass());

        $resolver = new RequestHandlerResolver($container);
        $this->expectExceptionCode(UnresolvedException::class);
        $this->expectExceptionCode(3);

        $resolver->resolve($containerId);
    }
}
