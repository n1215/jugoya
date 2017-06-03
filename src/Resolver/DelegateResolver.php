<?php

namespace N1215\Jugoya\Resolver;

use Interop\Http\ServerMiddleware\DelegateInterface;
use N1215\Jugoya\Wrapper\CallableDelegate;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Container\ContainerExceptionInterface;

class DelegateResolver implements DelegateResolverInterface
{

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
            throw new \InvalidArgumentException("Argument 1 \$ref must be one of an instance of DelegateInterface, a callable or string.");
        }

        try {
            $entry = $this->container->get($ref);
        } catch (NotFoundExceptionInterface $e) {
            throw new UnresolvedException('Could not found an entry from the container.', 0, $e);
        } catch (ContainerExceptionInterface $e) {
            throw new UnresolvedException('Something wrong with the container.', 0, $e);
        }

        if (!$entry instanceof DelegateInterface) {
            $type = is_object($entry) ? get_class($entry) : gettype($entry);
            throw new UnresolvedException("Expected container returns an instance of DelegateInterface, {$type} given.");
        }

        return $entry;
    }
}
