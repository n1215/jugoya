<?php

namespace N1215\Jugoya;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class MiddlewarePipeline
 */
class MiddlewarePipeline implements MiddlewareInterface
{

    /**
     * @var MiddlewareInterface[]
     */
    private $queue = [];

    /**
     * @param MiddlewareInterface[] $queue
     */
    public function __construct(array $queue)
    {
        foreach ($queue as $middleware) {
            $this->add($middleware);
        }
    }

    /**
     * @param MiddlewareInterface $middleware
     * @return MiddlewarePipeline
     */
    private function add(MiddlewareInterface $middleware)
    {
        $this->queue[] = $middleware;
        return $this;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        /** @var DelegateInterface $pipelineDelegate */
        $pipelineDelegate = array_reduce(
            array_reverse($this->queue),
            function(DelegateInterface $delegate, MiddlewareInterface $middleware) {
                return new MiddlewareWrappedDelegate($delegate, $middleware);
            },
            $delegate
        );

        return $pipelineDelegate->process($request);
    }
}
