<?php

namespace N1215\Jugoya;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;
use N1215\Jugoya\Resolver\RequestHandlerResolver;
use N1215\Jugoya\Resolver\RequestHandlerResolverInterface;
use N1215\Jugoya\Resolver\MiddlewareResolver;
use N1215\Jugoya\Resolver\MiddlewareResolverInterface;
use N1215\Jugoya\Resolver\UnresolvedException;
use Psr\Container\ContainerInterface;

final class LazyRequestHandlerBuilder implements RequestHandlerBuilderInterface
{
    /**
     * @var RequestHandlerResolverInterface
     */
    private $handlerResolver;

    /**
     * @var MiddlewareResolverInterface
     */
    private $middlewareResolver;

    /**
     * @param RequestHandlerResolverInterface $handlerResolver
     * @param MiddlewareResolverInterface $middlewareResolver
     */
    public function __construct(
        RequestHandlerResolverInterface $handlerResolver,
        MiddlewareResolverInterface $middlewareResolver
    ) {
        $this->handlerResolver = $handlerResolver;
        $this->middlewareResolver = $middlewareResolver;
    }

    /**
     * @param ContainerInterface $container
     * @return static
     */
    public static function fromContainer(ContainerInterface $container)
    {
        return new static(new RequestHandlerResolver($container), new MiddlewareResolver($container));
    }

    /**
     * @param RequestHandlerInterface|callable|string $coreHandlerRef
     * @param MiddlewareInterface[]|callable[]|string[] $middlewareRefs
     * @return RequestHandlerInterface
     */
    public function build($coreHandlerRef, array $middlewareRefs): RequestHandlerInterface
    {
        return new LazyDelegateHandler(
            $this->handlerResolver,
            $this->middlewareResolver,
            $coreHandlerRef,
            $middlewareRefs
        );
    }
}
