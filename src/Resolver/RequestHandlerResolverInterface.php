<?php

namespace N1215\Jugoya\Resolver;

use Interop\Http\Server\RequestHandlerInterface;

interface RequestHandlerResolverInterface
{

    /**
     * @param string|callable|RequestHandlerInterface $ref
     * @return RequestHandlerInterface
     * @throws UnresolvedException
     */
    public function resolve($ref);
}
