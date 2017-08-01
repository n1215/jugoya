<?php

namespace N1215\Jugoya;

use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\TextResponse;

class FakeHandler implements HandlerInterface
{

    /**
     * @var string
     */
    private $text;

    /**
     * @param string $text
     */
    public function __construct($text)
    {
        $this->text = $text;
    }

    /**
     * @param ServerRequestInterface $request
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request)
    {
        return new TextResponse($this->text);
    }

}