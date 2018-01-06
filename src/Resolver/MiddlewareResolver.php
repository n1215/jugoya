<?php

namespace N1215\Jugoya\Resolver;

use Interop\Http\Server\MiddlewareInterface;
use N1215\Jugoya\Wrapper\CallableMiddleware;
use Psr\Container\ContainerInterface;

final class MiddlewareResolver implements MiddlewareResolverInterface
{
    use ResolverTrait;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param string|callable|MiddlewareInterface $ref
     * @return MiddlewareInterface
     * @throws UnresolvedException
     */
    public function resolve($ref): MiddlewareInterface
    {
        if ($ref instanceof MiddlewareInterface) {
            return $ref;
        }

        if (is_callable($ref)) {
            return new CallableMiddleware($ref);
        }

        if (!is_string($ref)) {
            throw new \InvalidArgumentException('Argument 1 $ref must be one of an instance of MiddlewareInterface, a callable or string.');
        }

        return $this->resolveByContainer($ref, $this->container, MiddlewareInterface::class);
    }
}
