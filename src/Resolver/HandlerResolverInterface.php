<?php

namespace N1215\Jugoya\Resolver;

use N1215\Jugoya\HandlerInterface;

interface HandlerResolverInterface
{

    /**
     * @param string|callable|HandlerInterface $ref
     * @return HandlerInterface
     * @throws UnresolvedException
     */
    public function resolve($ref);
}
