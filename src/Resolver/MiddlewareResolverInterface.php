<?php

namespace N1215\Jugoya\Resolver;

use N1215\Jugoya\MiddlewareInterface;

interface MiddlewareResolverInterface
{

    /**
     * @param string|callable|MiddlewareInterface $ref
     * @return MiddlewareInterface
     * @throws UnresolvedException
     */
    public function resolve($ref);
}
