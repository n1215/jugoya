<?php

namespace N1215\Jugoya\Resolver;

use Interop\Http\Server\RequestHandlerInterface;
use N1215\Jugoya\Wrapper\CallableHandler;
use Psr\Container\ContainerInterface;

class RequestHandlerResolver implements RequestHandlerResolverInterface
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
     * @param string|callable|RequestHandlerInterface $ref
     * @return RequestHandlerInterface
     * @throws UnresolvedException
     */
    public function resolve($ref)
    {
        if ($ref instanceof RequestHandlerInterface) {
            return $ref;
        }

        if (is_callable($ref)) {
            return new CallableHandler($ref);
        }

        if (!is_string($ref)) {
            throw new \InvalidArgumentException('Argument 1 $ref must be one of an instance of HandlerInterface, a callable or string.');
        }

        return $this->resolveByContainer($ref, $this->container, RequestHandlerInterface::class);
    }
}
