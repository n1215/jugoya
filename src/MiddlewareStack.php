<?php

namespace N1215\Jugoya;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MiddlewareStack implements MiddlewareInterface
{

    /**
     * @var MiddlewareInterface[]
     */
    private $stack = [];

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
     * @return MiddlewareStack
     */
    private function add(MiddlewareInterface $middleware)
    {
        $this->stack[] = $middleware;
        return $this;
    }

    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        /** @var DelegateInterface $stackDelegate */
        $stackDelegate = array_reduce(
            array_reverse($this->stack),
            function(DelegateInterface $delegate, MiddlewareInterface $middleware) {
                return new MiddlewareWrappedDelegate($delegate, $middleware);
            },
            $delegate
        );

        return $stackDelegate->process($request);
    }
}
