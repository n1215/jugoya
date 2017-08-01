<?php

namespace N1215\Jugoya;

use N1215\Jugoya\Resolver\HandlerResolver;
use N1215\Jugoya\Resolver\HandlerResolverInterface;
use N1215\Jugoya\Resolver\MiddlewareResolver;
use N1215\Jugoya\Resolver\MiddlewareResolverInterface;
use N1215\Jugoya\Resolver\UnresolvedException;
use Psr\Container\ContainerInterface;

class HttpHandlerFactory
{

    /**
     * @var HandlerResolverInterface
     */
    private $handlerResolver;

    /**
     * @var MiddlewareResolverInterface
     */
    private $middlewareResolver;

    /**
     * @param HandlerResolverInterface $handlerResolver
     * @param MiddlewareResolverInterface $middlewareResolver
     */
    public function __construct(
        HandlerResolverInterface $handlerResolver,
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
        return new static(new HandlerResolver($container), new MiddlewareResolver($container));
    }

    /**
     * @param HandlerInterface|callable|string $coreHandlerRef
     * @param MiddlewareInterface[]|callable[]|string[] $middlewareRefs
     * @return HttpHandler
     * @throws UnresolvedException
     */
    public function create($coreHandlerRef, array $middlewareRefs)
    {
        $coreHandler = $this->handlerResolver->resolve($coreHandlerRef);

        /**
         * @var MiddlewareInterface[] $middlewareStack
         */
        $middlewareStack = array_map(function($ref) {
            return $this->middlewareResolver->resolve($ref);
        }, $middlewareRefs);

        return new HttpHandler($coreHandler, $middlewareStack);
    }
}
