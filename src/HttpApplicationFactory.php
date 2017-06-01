<?php

namespace N1215\Jugoya;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use N1215\Jugoya\Resolver\DelegateResolver;
use N1215\Jugoya\Resolver\DelegateResolverInterface;
use N1215\Jugoya\Resolver\MiddlewareResolver;
use N1215\Jugoya\Resolver\MiddlewareResolverInterface;
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
     * @param DelegateInterface|callable|string $coreDelegateEntry
     * @param MiddlewareInterface[]|callable[]|string[] $middlewareEntries
     * @return HttpApplication
     */
    public function create($coreDelegateEntry, array $middlewareEntries)
    {
        $coreDelegate = $this->delegateResolver->resolve($coreDelegateEntry);

        /**
         * @var MiddlewareInterface[] $middlewareQueue
         */
        $middlewareQueue = array_map(function($entry) {
            return $this->middlewareResolver->resolve($entry);
        }, $middlewareEntries);

        return new HttpApplication($coreDelegate, new MiddlewareStack($middlewareQueue));
    }
}
