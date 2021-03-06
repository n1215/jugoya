<?php

namespace N1215\Jugoya;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class FakeMiddleware implements MiddlewareInterface
{

    const ATTRIBUTE_KEY = 'fake_middleware';

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
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $attribute = $request->getAttribute(self::ATTRIBUTE_KEY);
        $newRequest = $request->withAttribute(self::ATTRIBUTE_KEY, $attribute . $this->text . PHP_EOL);

        $response = $handler->handle($newRequest);

        $body = $response->getBody();
        $body->seek($body->getSize());
        $body->write(PHP_EOL . $this->text);
        return $response;
    }
}
