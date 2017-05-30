<?php

namespace N1215\Jugoya;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CallableDelegate implements DelegateInterface
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
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request)
    {
        $response = call_user_func($this->callable, $request);

        if (!$response instanceof ResponseInterface) {
            throw new \LogicException('callable must return an instance of Psr\Http\Message\ResponseInterface.');
        }

        return $response;
    }
}
