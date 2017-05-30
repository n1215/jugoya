# Jugoya
A PSR-15 HTTP application builder.

# Example

        // 1. register middleware dependencies to the PSR-11 Container
        /** @var \Psr\Container\ContainerInterface $container */
        $container = new YourContainer();
        //
        // do stuff
        //


        // 2. create an application builder
        $builder = new \N1215\Jugoya\Jugoya(new \N1215\Jugoya\MiddlewareResolver($container));


        // 3. build an http application
        /** @var DelegateInterface|callable $coreDelegate */
        $coreDelegate = new YourApplication();

        /** @var HttpApplication|DelegateInterface $app */
        $app = $builder
            ->from($coreDelegate)
            ->middleware([
                // a callable having the same signature with PSR-15 MiddlewareInterface
                function(ServerRequestInterface $request, DelegateInterface $delegate) {
                    // do stuff
                    $response = $delegate->process($request);
                    // do stuff
                    return $response;
                },

                // an instance of PSR-15 MiddlewareInterface
                new YourMiddleware(),
            ])
            ->middleware([
                // a key string for a PSR-15 MiddlewareInterface in the PSR-11 Container
                YourMiddleware::class,
            ])
            ->build();


        // 4. handle a PSR-7 Sever Request
        /** @var Psr\Http\Message\ServerRequestInterface $request */
        $request = \Zend\Diactoros\ServerRequestFactory::fromGlobals();
        /** @var \Psr\Http\Message\ResponseInterface $response */
        $response = $app->process($request);

