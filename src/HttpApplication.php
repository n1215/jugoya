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
     * @var MiddlewarePipeline
     */
    private $middlewarePipeline;

    /**
     * @param DelegateInterface $coreDelegate
     * @param MiddlewarePipeline $middlewarePipeline
     */
    public function __construct(DelegateInterface $coreDelegate, MiddlewarePipeline $middlewarePipeline)
    {
        $this->coreDelegate = $coreDelegate;
        $this->middlewarePipeline = $middlewarePipeline;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request)
    {
        return $this->middlewarePipeline->process($request, $this->coreDelegate);
    }
}
