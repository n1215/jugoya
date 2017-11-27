<?php

namespace N1215\Jugoya\Resolver;

use Interop\Http\Server\MiddlewareInterface;

interface MiddlewareResolverInterface
{

    /**
     * @param string|callable|MiddlewareInterface $ref
     * @return MiddlewareInterface
     * @throws UnresolvedException
     */
    public function resolve($ref);
}
