<?php

namespace N1215\Jugoya\Resolver;

use Interop\Http\ServerMiddleware\MiddlewareInterface;
use N1215\Jugoya\Wrapper\CallableMiddleware;

class MiddlewareResolver extends ResolverAbstract implements MiddlewareResolverInterface
{

    /**
     * @param string|callable|MiddlewareInterface $ref
     * @return MiddlewareInterface
     * @throws UnresolvedException
     */
    public function resolve($ref)
    {
        return $this->resolveWithType($ref, MiddlewareInterface::class, CallableMiddleware::class);
    }
}
