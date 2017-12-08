<?php

namespace N1215\Jugoya\Resolver;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;
use N1215\Jugoya\Wrapper\CallableMiddleware;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MiddlewareResolverTest extends TestCase
{

    public function testResolveForMiddlewareInterface()
    {
        /** @var MiddlewareInterface $middleware */
        $middleware = $this->createMock(MiddlewareInterface::class);
        /** @var ContainerInterface $container */
        $container = $this->createMock(ContainerInterface::class);

        $resolver = new MiddlewareResolver($container);
        $resolved = $resolver->resolve($middleware);

        $this->assertEquals($middleware, $resolved);
    }

    public function testResolveForCallable()
    {
        /** @var ContainerInterface $container */
        $container = $this->createMock(ContainerInterface::class);
        $resolver = new MiddlewareResolver($container);

        $callable = function(ServerRequestInterface $request, RequestHandlerInterface $handler) {
            return $this->createMock(ResponseInterface::class);
        };

        $resolved = $resolver->resolve($callable);
        $this->assertInstanceOf(CallableMiddleware::class, $resolved);
    }

    public function testResolveByContainer()
    {
        /** @var MiddlewareInterface $middleware */
        $middleware = $this->createMock(MiddlewareInterface::class);
        $containerId = 'dummyId';

        /** @var ContainerInterface|MockObject $container */
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
            ->method('get')
            ->with($containerId)
            ->willReturn($middleware);
        $resolver = new MiddlewareResolver($container);

        $resolved = $resolver->resolve($containerId);

        $this->assertEquals($resolved, $middleware);
    }

    public function testResolveFailure()
    {
        /** @var ContainerInterface $container */
        $container = $this->createMock(ContainerInterface::class);
        $resolver = new MiddlewareResolver($container);

        $ref = 123456789;
        $this->expectException(\InvalidArgumentException::class);

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

        $resolver = new MiddlewareResolver($container);
        $this->expectException(UnresolvedException::class);
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

        $resolver = new MiddlewareResolver($container);
        $this->expectException(UnresolvedException::class);
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
            ->willReturn(new \stdClass());

        $resolver = new MiddlewareResolver($container);
        $this->expectException(UnresolvedException::class);
        $this->expectExceptionCode(3);

        $resolver->resolve($containerId);
    }
}
