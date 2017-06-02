<?php

namespace N1215\Jugoya;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HttpApplication implements DelegateInterface
{

    /**
     * @var DelegateInterface
     */
    private $coreDelegate;

    /**
     * @var MiddlewareInterface[]
     */
    private $middlewareStack = [];

    /**
     * @param DelegateInterface $coreDelegate
     * @param MiddlewareInterface[] $middlewareStack
     */
    public function __construct(DelegateInterface $coreDelegate, array $middlewareStack)
    {
        $this->coreDelegate = $coreDelegate;

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
    public function process(ServerRequestInterface $request)
    {
        $count = count($this->middlewareStack);

        if ($count === 0) {
            return $this->coreDelegate->process($request);
        }

        if ($count === 1) {
            return $this->middlewareStack[0]->process($request, $this->coreDelegate);
        }

        /** @var DelegateInterface $delegate */
        $delegate = array_reduce(
            array_reverse($this->middlewareStack),
            function(DelegateInterface $delegate, MiddlewareInterface $middleware) {
                return new HttpApplication($delegate, [$middleware]);
            },
            $this->coreDelegate
        );

        return $delegate->process($request);
    }
}
