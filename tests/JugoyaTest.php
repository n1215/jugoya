<?php

namespace N1215\Jugoya;

use Interop\Http\ServerMiddleware\DelegateInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\TextResponse;
use Zend\Diactoros\ServerRequest;

class JugoyaTest extends TestCase
{

    protected function tearDown()
    {
        parent::tearDown();
        \Mockery::close();
    }

    /**
     * @param DelegateInterface|callable $coreDelegate
     * @dataProvider dataProviderCoreDelegate
     */
    public function testBuild($coreDelegate)
    {
        /** @var ContainerInterface $container */
        $container = \Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('get')
            ->with(FakeMiddleware::class)
            ->andReturn(new FakeMiddleware('dependency'));

        $container->shouldReceive('get')
            ->with(FakeDelegate::class)
            ->andReturn(new FakeDelegate('delegate'));

        $appBuilder = Jugoya::fromContainer($container);

        $app = $appBuilder->build($coreDelegate, [
            function(ServerRequestInterface $request, DelegateInterface $delegate) {
                $response = $delegate->process($request);
                $body = $response->getBody();
                $body->seek($body->getSize());
                $body->write(PHP_EOL . 'callable');
                return $response;
            },
            new FakeMiddleware('object'),
            FakeMiddleware::class,
        ]);

        $this->assertInstanceOf(HttpApplication::class, $app);

        $request = new ServerRequest();
        $response = $app->process($request);

        $expected = join(PHP_EOL, ['delegate', 'dependency', 'object', 'callable']);
        $this->assertEquals($expected, $response->getBody()->__toString());
    }

    public function dataProviderCoreDelegate()
    {
        return [
            [new FakeDelegate('delegate')],

            [function(ServerRequestInterface $request) {
                return new TextResponse('delegate');
            }],

            [FakeDelegate::class],
        ];
    }

    public function testFromContainer()
    {
        /** @var ContainerInterface $container */
        $container = \Mockery::mock(ContainerInterface::class);
        $appBuilder = Jugoya::fromContainer($container);
        $this->assertInstanceOf(Jugoya::class, $appBuilder);
    }

}