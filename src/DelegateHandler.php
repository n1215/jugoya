<?php

namespace N1215\Jugoya;

use Interop\Http\Server\MiddlewareInterface;
use Interop\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class DelegateHandler implements RequestHandlerInterface
{

    /**
     * @var RequestHandlerInterface
     */
    private $coreHandler;

    /**
     * @var MiddlewareInterface[]
     */
    private $middlewareStack = [];

    /**
     * @param RequestHandlerInterface $coreHandler
     * @param MiddlewareInterface[] $middlewareStack
     */
    public function __construct(RequestHandlerInterface $coreHandler, array $middlewareStack)
    {
        $this->coreHandler = $coreHandler;

        foreach ($middlewareStack as $middleware) {
            $this->addMiddleware($middleware);
        }
    }

    /**
     * @param MiddlewareInterface $middleware
     */
    private function addMiddleware(MiddlewareInterface $middleware)
    {
        $this->middlewareStack[] = $middleware;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (count($this->middlewareStack) === 0) {
            return $this->coreHandler->handle($request);
        }

        $headMiddleware = array_shift($this->middlewareStack);
        return $headMiddleware->process($request, $this);
    }
}
