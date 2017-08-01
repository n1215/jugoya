<?php

namespace N1215\Jugoya;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HttpHandler implements HandlerInterface
{

    /**
     * @var HandlerInterface
     */
    private $coreHandler;

    /**
     * @var MiddlewareInterface[]
     */
    private $middlewareStack = [];

    /**
     * @param HandlerInterface $coreHandler
     * @param MiddlewareInterface[] $middlewareStack
     */
    public function __construct(HandlerInterface $coreHandler, array $middlewareStack)
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
    public function __invoke(ServerRequestInterface $request)
    {
        $count = count($this->middlewareStack);

        if ($count === 0) {
            return $this->coreHandler->__invoke($request);
        }

        if ($count === 1) {
            return $this->middlewareStack[0]->process($request, $this->coreHandler);
        }

        /** @var HandlerInterface $handler */
        $handler = array_reduce(
            array_reverse($this->middlewareStack),
            function(HandlerInterface $handler, MiddlewareInterface $middleware) {
                return new HttpHandler($handler, [$middleware]);
            },
            $this->coreHandler
        );

        return $handler->__invoke($request);
    }
}
