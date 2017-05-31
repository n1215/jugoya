<?php

namespace N1215\Jugoya\Resolver;

use Interop\Http\ServerMiddleware\DelegateInterface;

interface DelegateResolverInterface
{

    /**
     * @param string|callable|MiddlewareInterface
     * @return DelegateInterface
     */
    public function resolve($entry);
}
