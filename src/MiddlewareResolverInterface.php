<?php

namespace N1215\Jugoya;

use Interop\Http\ServerMiddleware\MiddlewareInterface;

/**
 * Interface MiddlewareResolverInterface
 * @package N1215\Jugoya
 */
interface MiddlewareResolverInterface
{
    /**
     * @param string|callable|MiddlewareInterface
     * @return MiddlewareInterface
     */
    public function resolve($entry);
}
