<?php

namespace N1215\Jugoya\Wrapper;

use N1215\Jugoya\HandlerInterface;
use N1215\Jugoya\MiddlewareInterface;
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
     * @param HandlerInterface $delegate
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, HandlerInterface $delegate)
    {
        $response = call_user_func($this->callable, $request, $delegate);

        if (!$response instanceof ResponseInterface) {
            throw new \LogicException('callable must return an instance of \Psr\Http\Message\ResponseInterface.');
        }

        return $response;
    }
}
