<?php

namespace N1215\Jugoya;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
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
        $count = count($this->middlewareRefs);
        if ($count === 0) {
            $coreHandler = $this->handlerResolver->resolve($this->coreHandlerRef);
            return $coreHandler->handle($request);
        }

        $headMiddleware = $this->middlewareResolver->resolve($this->middlewareRefs[0]);

        $innerDelegate = new self(
            $this->handlerResolver,
            $this->middlewareResolver,
            $this->coreHandlerRef,
            array_slice($this->middlewareRefs, 1, $count - 1)
        );

        return $headMiddleware->process($request, $innerDelegate);
    }
}
