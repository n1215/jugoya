<?php

namespace N1215\Jugoya\Resolver;

use Interop\Http\ServerMiddleware\DelegateInterface;
use N1215\Jugoya\Wrapper\CallableDelegate;
use Psr\Container\ContainerInterface;

class DelegateResolver implements DelegateResolverInterface
{

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param string|callable|DelegateInterface $entry
     * @return DelegateInterface
     */
    public function resolve($entry)
    {
        if ($entry instanceof DelegateInterface) {
            return $entry;
        }

        if (is_callable($entry)) {
            return new CallableDelegate($entry);
        }

        if (!is_string($entry)) {
            throw new \InvalidArgumentException('$entry must be one of a DelegateInterface, a callable or string');
        }

        return $this->container->get($entry);
    }
}
