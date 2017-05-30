<?php

namespace N1215\Jugoya;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MiddlewareWrappedDelegate implements DelegateInterface
{

    /**
     * @var DelegateInterface
     */
    private $delegate;

    /**
     * @var MiddlewareInterface
     */
    private $middleware;

    /**
     * @param DelegateInterface $delegate
     * @param MiddlewareInterface $middleware
     */
    public function __construct(DelegateInterface $delegate, MiddlewareInterface $middleware)
    {
        $this->delegate = $delegate;
        $this->middleware = $middleware;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request)
    {
        return $this->middleware->process($request, $this->delegate);
    }
}
