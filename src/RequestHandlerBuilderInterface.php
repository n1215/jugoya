<?php

namespace N1215\Jugoya;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;

interface RequestHandlerBuilderInterface
{
    /**
     * @param RequestHandlerInterface|callable|string $coreHandlerRef
     * @param MiddlewareInterface[]|callable[]|string[] $middlewareRefs
     * @return RequestHandlerInterface
     */
    public function build($coreHandlerRef, array $middlewareRefs): RequestHandlerInterface;
}
