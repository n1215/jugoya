<?php

namespace N1215\Jugoya;

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
     * @param HandlerInterface $delegate
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(ServerRequestInterface $request, HandlerInterface $delegate)
    {
        $attribute = $request->getAttribute(self::ATTRIBUTE_KEY);
        $newRequest = $request->withAttribute(self::ATTRIBUTE_KEY, $attribute . $this->text . PHP_EOL);

        $response = $delegate->__invoke($newRequest);

        $body = $response->getBody();
        $body->seek($body->getSize());
        $body->write(PHP_EOL . $this->text);
        return $response;
    }

}