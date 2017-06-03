<?php

namespace N1215\Jugoya\Resolver;

use Interop\Http\ServerMiddleware\DelegateInterface;
use N1215\Jugoya\Wrapper\CallableDelegate;
use Psr\Container\ContainerInterface;

class DelegateResolver implements DelegateResolverInterface
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
     * @param string|callable|DelegateInterface $ref
     * @return DelegateInterface
     * @throws UnresolvedException
     */
    public function resolve($ref)
    {
        if ($ref instanceof DelegateInterface) {
            return $ref;
        }

        if (is_callable($ref)) {
            return new CallableDelegate($ref);
        }

        if (!is_string($ref)) {
            throw new \InvalidArgumentException('Argument 1 $ref must be one of an instance of DelegateInterface, a callable or string.');
        }

        return $this->resolveByContainer($ref, $this->container, DelegateInterface::class);
    }
}
