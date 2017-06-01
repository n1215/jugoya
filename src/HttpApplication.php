<?php

namespace N1215\Jugoya;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HttpApplication implements DelegateInterface
{

    /**
     * @var DelegateInterface
     */
    private $coreDelegate;

    /**
     * @var MiddlewareStack
     */
    private $middlewareStack;

    /**
     * @param DelegateInterface $coreDelegate
     * @param MiddlewareStack $middlewareStack
     */
    public function __construct(DelegateInterface $coreDelegate, MiddlewareStack $middlewareStack)
    {
        $this->coreDelegate = $coreDelegate;
        $this->middlewareStack = $middlewareStack;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request)
    {
        return $this->middlewareStack->process($request, $this->coreDelegate);
    }
}
