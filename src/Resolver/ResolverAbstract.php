<?php

namespace N1215\Jugoya\Resolver;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Container\ContainerExceptionInterface;

class ResolverAbstract
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
     * @param string|callable|mixed $ref
     * @param string $returnType
     * @param string $callableWrapperClass
     * @return mixed
     * @throws UnresolvedException
     */
    protected function resolveWithType($ref, $returnType, $callableWrapperClass)
    {
        if ($ref instanceof $returnType) {
            return $ref;
        }

        if (is_callable($ref)) {
            return new $callableWrapperClass($ref);
        }

        if (!is_string($ref)) {
            throw new \InvalidArgumentException("Argument 1 \$ref must be one of an instance of {$returnType}, a callable or string.");
        }

        try {
            $entry = $this->container->get($ref);
        } catch (NotFoundExceptionInterface $e) {
            throw new UnresolvedException('Could not found an entry from the container.', 0, $e);
        } catch (ContainerExceptionInterface $e) {
            throw new UnresolvedException('Something wrong with the container.', 0, $e);
        }

        if (! $entry instanceof $returnType) {
            $type = gettype($entry);
            throw new UnresolvedException("Expected container returns an instance of {$returnType}, {$type} given.");
        }

        return $entry;
    }
}