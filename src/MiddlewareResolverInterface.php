<?php

namespace N1215\Jugoya;

use Interop\Http\ServerMiddleware\MiddlewareInterface;

interface MiddlewareResolverInterface
{

    /**
     * @param string|callable|MiddlewareInterface
     * @return MiddlewareInterface
     */
    public function resolve($entry);
}
