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

class RequestHandlerFactory implements RequestHandlerFactoryInterface
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
     * @throws UnresolvedException
     */
    public function create($coreHandlerRef, array $middlewareRefs): RequestHandlerInterface
    {
        $coreHandler = $this->handlerResolver->resolve($coreHandlerRef);

        /**
         * @var MiddlewareInterface[] $middlewareStack
         */
        $middlewareStack = array_map(function($ref) {
            return $this->middlewareResolver->resolve($ref);
        }, $middlewareRefs);

        return new RequestHandler($coreHandler, $middlewareStack);
    }
}
