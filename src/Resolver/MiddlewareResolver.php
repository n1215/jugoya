<?php

namespace N1215\Jugoya\Resolver;

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use N1215\Jugoya\Wrapper\CallableMiddleware;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Container\ContainerExceptionInterface;

class MiddlewareResolver implements MiddlewareResolverInterface
{

    /**
     * @param string|callable|MiddlewareInterface $ref
     * @return MiddlewareInterface
     * @throws UnresolvedException
     */
    public function resolve($ref)
    {
        if ($ref instanceof MiddlewareInterface) {
            return $ref;
        }

        if (is_callable($ref)) {
            return new CallableMiddleware($ref);
        }

        if (!is_string($ref)) {
            throw new \InvalidArgumentException("Argument 1 \$ref must be one of an instance of MiddlewareInterface, a callable or string.");
        }

        try {
            $entry = $this->container->get($ref);
        } catch (NotFoundExceptionInterface $e) {
            throw new UnresolvedException('Could not found an entry from the container.', 0, $e);
        } catch (ContainerExceptionInterface $e) {
            throw new UnresolvedException('Something wrong with the container.', 0, $e);
        }

        if (!$entry instanceof MiddlewareInterface) {
            $type = is_object($entry) ? get_class($entry) : gettype($entry);
            throw new UnresolvedException("Expected container returns an instance of MiddlewareInterface, {$type} given.");
        }

        return $entry;
    }
}
