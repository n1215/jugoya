<?php

namespace N1215\Jugoya;

use Psr\Http\Server\RequestHandlerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\TextResponse;
use Zend\Diactoros\ServerRequest;

class LazyRequestHandlerBuilderTest extends TestCase
{
    /**
     * @param RequestHandlerInterface|callable $coreHandler
     * @dataProvider dataProviderCoreHandler
     */
    public function testCreate($coreHandler)
    {
        /** @var ContainerInterface|MockObject $container */
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap([
                [FakeMiddleware::class, new FakeMiddleware('dependency')],
                [FakeHandler::class, new FakeHandler('handler')],
            ]));

        $builder = LazyRequestHandlerBuilder::fromContainer($container);

        $app = $builder->build($coreHandler, [
            function(ServerRequestInterface $request, RequestHandlerInterface $handler) {
                $response = $handler->handle($request);
                $body = $response->getBody();
                $body->seek($body->getSize());
                $body->write(PHP_EOL . 'callable');
                return $response;
            },
            new FakeMiddleware('object'),
            FakeMiddleware::class,
        ]);

        $this->assertInstanceOf(LazyDelegateHandler::class, $app);

        $request = new ServerRequest();
        $response = $app->handle($request);

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
        $container = $this->createMock(ContainerInterface::class);
        $builder = LazyRequestHandlerBuilder::fromContainer($container);
        $this->assertInstanceOf(LazyRequestHandlerBuilder::class, $builder);
    }
}
