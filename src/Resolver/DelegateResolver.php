<?php

namespace N1215\Jugoya\Resolver;

use Interop\Http\ServerMiddleware\DelegateInterface;
use N1215\Jugoya\Wrapper\CallableDelegate;

class DelegateResolver extends ResolverAbstract implements DelegateResolverInterface
{

    /**
     * @param string|callable|DelegateInterface $ref
     * @return DelegateInterface
     * @throws UnresolvedException
     */
    public function resolve($ref)
    {
        return $this->resolveWithType($ref, DelegateInterface::class, CallableDelegate::class);
    }
}
