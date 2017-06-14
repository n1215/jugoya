<?php

namespace N1215\Jugoya;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use N1215\Jugoya\Resolver\DelegateResolver;
use N1215\Jugoya\Resolver\DelegateResolverInterface;
use N1215\Jugoya\Resolver\MiddlewareResolver;
use N1215\Jugoya\Resolver\MiddlewareResolverInterface;
use N1215\Jugoya\Resolver\UnresolvedException;
use Psr\Container\ContainerInterface;

class HttpApplicationFactory
{

    /**
     * @var DelegateResolverInterface
     */
    private $delegateResolver;

    /**
     * @var MiddlewareResolverInterface
     */
    private $middlewareResolver;

    /**
     * @param DelegateResolverInterface $delegateResolver
     * @param MiddlewareResolverInterface $middlewareResolver
     */
    public function __construct(
        DelegateResolverInterface $delegateResolver,
        MiddlewareResolverInterface $middlewareResolver
    ) {
        $this->delegateResolver = $delegateResolver;
        $this->middlewareResolver = $middlewareResolver;
    }

    /**
     * @param ContainerInterface $container
     * @return static
     */
    public static function fromContainer(ContainerInterface $container)
    {
        return new static(new DelegateResolver($container), new MiddlewareResolver($container));
    }

    /**
     * @param DelegateInterface|callable|string $coreDelegateRef
     * @param MiddlewareInterface[]|callable[]|string[] $middlewareRefs
     * @return HttpApplication
     * @throws UnresolvedException
     */
    public function create($coreDelegateRef, array $middlewareRefs)
    {
        $coreDelegate = $this->delegateResolver->resolve($coreDelegateRef);

        /**
         * @var MiddlewareInterface[] $middlewareStack
         */
        $middlewareStack = array_map(function($ref) {
            return $this->middlewareResolver->resolve($ref);
        }, $middlewareRefs);

        return new HttpApplication($coreDelegate, $middlewareStack);
    }
}
