<?php

namespace N1215\Jugoya;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface MiddlewareInterface
{
    /**
     * @param ServerRequestInterface $request
     * @param HandlerInterface $delegate
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, HandlerInterface $delegate);
}
