<?php

namespace N1215\Jugoya;

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Container\ContainerInterface;

class MiddlewareResolver implements MiddlewareResolverInterface
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
     * @param string|callable|MiddlewareInterface $entry
     * @return MiddlewareInterface
     */
    public function resolve($entry)
    {
        if ($entry instanceof MiddlewareInterface) {
            return $entry;
        }

        if (is_callable($entry)) {
            return new CallableMiddleware($entry);
        }

        if (!is_string($entry)) {
            throw new \InvalidArgumentException('$entry must be one of a MiddlewareInterface, a callable or string');
        }

        return $this->container->get($entry);
    }
}
