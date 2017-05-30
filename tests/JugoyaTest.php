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

        $appBuilder = new Jugoya(new MiddlewareResolver($container));

        $app = $appBuilder
            ->from($coreDelegate)
            ->middleware([
                function(ServerRequestInterface $request, DelegateInterface $delegate) {
                    $response = $delegate->process($request);
                    $body = $response->getBody();
                    $body->seek($body->getSize());
                    $body->write(PHP_EOL . 'callable');
                    return $response;
                },
                new FakeMiddleware('object'),
            ])
            ->middleware([
                FakeMiddleware::class,
            ])
            ->build();

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
            }]
        ];
    }

    /**
     * @expectedException \LogicException
     */
    public function testBuildThrowsExceptionWhenCalledBeforeSettingCoreDelegate()
    {
        /** @var ContainerInterface $container */
        $container = \Mockery::mock(ContainerInterface::class);

        $appBuilder = new Jugoya(new MiddlewareResolver($container));

        $appBuilder->build();
    }


    /**
     * @expectedException \LogicException
     */
    public function testBuildThrowsExceptionForInvalidCoreDelegate()
    {
        /** @var ContainerInterface $container */
        $container = \Mockery::mock(ContainerInterface::class);
        $appBuilder = new Jugoya(new MiddlewareResolver($container));
        $coreDelegate = new \stdClass();

        $appBuilder->from($coreDelegate)->build();
    }

}