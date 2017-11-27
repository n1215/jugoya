<?php

namespace N1215\Jugoya\Wrapper;

use Interop\Http\Server\RequestHandlerInterface;
use Interop\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CallableMiddleware implements MiddlewareInterface
{

    /**
     * @var callable
     */
    private $callable;

    /**
     * @param callable $callable
     */
    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler)
    {
        $response = call_user_func($this->callable, $request, $handler);

        if (!$response instanceof ResponseInterface) {
            throw new \LogicException('callable must return an instance of \Psr\Http\Message\ResponseInterface.');
        }

        return $response;
    }
}
