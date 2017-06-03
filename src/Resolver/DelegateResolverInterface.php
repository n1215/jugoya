<?php

namespace N1215\Jugoya\Resolver;

use Interop\Http\ServerMiddleware\DelegateInterface;

interface DelegateResolverInterface
{

    /**
     * @param string|callable|DelegateInterface $ref
     * @return DelegateInterface
     * @throws UnresolvedException
     */
    public function resolve($ref);
}
