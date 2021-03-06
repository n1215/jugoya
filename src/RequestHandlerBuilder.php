<?php

namespace N1215\Jugoya;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use N1215\Jugoya\Resolver\RequestHandlerResolver;
use N1215\Jugoya\Resolver\RequestHandlerResolverInterface;
use N1215\Jugoya\Resolver\MiddlewareResolver;
use N1215\Jugoya\Resolver\MiddlewareResolverInterface;
use N1215\Jugoya\Resolver\UnresolvedException;
use Psr\Container\ContainerInterface;

final class RequestHandlerBuilder implements RequestHandlerBuilderInterface
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
     * @return self
     */
    public static function fromContainer(ContainerInterface $container): self
    {
        return new self(new RequestHandlerResolver($container), new MiddlewareResolver($container));
    }

    /**
     * @param RequestHandlerInterface|callable|string $coreHandlerRef
     * @param MiddlewareInterface[]|callable[]|string[] $middlewareRefs
     * @return RequestHandlerInterface
     * @throws UnresolvedException
     */
    public function build($coreHandlerRef, array $middlewareRefs): RequestHandlerInterface
    {
        $coreHandler = $this->handlerResolver->resolve($coreHandlerRef);

        /**
         * @var MiddlewareInterface[] $middlewareStack
         */
        $middlewareStack = array_map(function($ref) {
            return $this->middlewareResolver->resolve($ref);
        }, $middlewareRefs);

        return new DelegateHandler($coreHandler, $middlewareStack);
    }
}
