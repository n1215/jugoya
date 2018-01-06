<?php

namespace N1215\Jugoya;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;
use N1215\Jugoya\Resolver\MiddlewareResolverInterface;
use N1215\Jugoya\Resolver\RequestHandlerResolverInterface;
use N1215\Jugoya\Resolver\UnresolvedException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class LazyDelegateHandler implements RequestHandlerInterface
{
    /**
     * @var RequestHandlerResolverInterface
     */
    private $handlerResolver;

    /**
     * @var MiddlewareResolverInterface
     */
    private $middlewareResolver;

    /**
     * @var RequestHandlerInterface|callable|string $coreHandlerRef
     */
    private $coreHandlerRef;

    /**
     * @var MiddlewareInterface[]|callable[]|string[]
     */
    private $middlewareRefs = [];

    /**
     * @param RequestHandlerResolverInterface $handlerResolver
     * @param MiddlewareResolverInterface $middlewareResolver
     * @param RequestHandlerInterface|callable|string $coreHandlerRef
     * @param MiddlewareInterface[]|callable[]|string[] $middlewareRefs
     */
    public function __construct(
        RequestHandlerResolverInterface $handlerResolver,
        MiddlewareResolverInterface $middlewareResolver,
        $coreHandlerRef,
        array $middlewareRefs
    ) {
        $this->handlerResolver = $handlerResolver;
        $this->middlewareResolver = $middlewareResolver;
        $this->coreHandlerRef = $coreHandlerRef;
        $this->middlewareRefs = $middlewareRefs;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws UnresolvedException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (count($this->middlewareRefs) === 0) {
            $coreHandler = $this->handlerResolver->resolve($this->coreHandlerRef);
            return $coreHandler->handle($request);
        }

        $headMiddleware = $this->middlewareResolver->resolve(array_shift($this->middlewareRefs));
        return $headMiddleware->process($request, $this);
    }
}
