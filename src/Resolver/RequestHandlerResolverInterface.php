<?php

namespace N1215\Jugoya\Resolver;

use Psr\Http\Server\RequestHandlerInterface;

interface RequestHandlerResolverInterface
{

    /**
     * @param string|callable|RequestHandlerInterface $ref
     * @return RequestHandlerInterface
     * @throws UnresolvedException
     */
    public function resolve($ref): RequestHandlerInterface;
}
