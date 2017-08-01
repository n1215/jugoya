<?php

namespace N1215\Jugoya;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\TextResponse;
use Zend\Diactoros\ServerRequest;

class HttpHandlerFactoryTest extends TestCase
{

    protected function tearDown()
    {
        parent::tearDown();
        \Mockery::close();
    }

    /**
     * @param HandlerInterface|callable $coreHandler
     * @dataProvider dataProviderCoreHandler
     */
    public function testCreate($coreHandler)
    {
        /** @var ContainerInterface $container */
        $container = \Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('get')
            ->with(FakeMiddleware::class)
            ->andReturn(new FakeMiddleware('dependency'));

        $container->shouldReceive('get')
            ->with(FakeHandler::class)
            ->andReturn(new FakeHandler('handler'));

        $factory = HttpHandlerFactory::fromContainer($container);

        $app = $factory->create($coreHandler, [
            function(ServerRequestInterface $request, HandlerInterface $delegate) {
                $response = $delegate->__invoke($request);
                $body = $response->getBody();
                $body->seek($body->getSize());
                $body->write(PHP_EOL . 'callable');
                return $response;
            },
            new FakeMiddleware('object'),
            FakeMiddleware::class,
        ]);

        $this->assertInstanceOf(HttpHandler::class, $app);

        $request = new ServerRequest();
        $response = $app->__invoke($request);

        $expected = join(PHP_EOL, ['handler', 'dependency', 'object', 'callable']);
        $this->assertEquals($expected, $response->getBody()->__toString());
    }

    public function dataProviderCoreHandler()
    {
        return [
            [new FakeHandler('handler')],

            [function(ServerRequestInterface $request) {
                return new TextResponse('handler');
            }],

            [FakeHandler::class],
        ];
    }

    public function testFromContainer()
    {
        /** @var ContainerInterface $container */
        $container = \Mockery::mock(ContainerInterface::class);
        $factory = HttpHandlerFactory::fromContainer($container);
        $this->assertInstanceOf(HttpHandlerFactory::class, $factory);
    }

}