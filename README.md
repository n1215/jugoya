# Jugoya（十五夜）🌕

[![Latest Stable Version](https://poser.pugx.org/n1215/jugoya/v/stable)](https://packagist.org/packages/n1215/jugoya)
[![License](https://poser.pugx.org/n1215/jugoya/license)](https://packagist.org/packages/n1215/jugoya)
[![Build Status](https://scrutinizer-ci.com/g/n1215/jugoya/badges/build.png?b=master)](https://scrutinizer-ci.com/g/n1215/jugoya/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/n1215/jugoya/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/n1215/jugoya/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/n1215/jugoya/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/n1215/jugoya/?branch=master)

A simple HTTP application builder using PSR-15 HTTP Server Request Handler and Middleware.

> Jugoya is the Japanese full moon festival on the 15th day of the eighth month of the traditional Japanese calendar.

## PSR-15 HTTP Server Handler and Middleware

![psr15_middleware](doc/psr15_middleware.png)

See [php-fig/fig-standards](https://github.com/php-fig/fig-standards/blob/master/proposed/http-middleware/middleware.md)


## What Jugoya does
Jugoya create a new instance of RequestHandlerInterface from a instance of RequestHandlerInterface and instances of MiddlewareInterface.
![composition](doc/composition.png)


## Code Example

```php
// 1. register handler and middleware dependencies to the PSR-11 Container
/** @var \Psr\Container\ContainerInterface $container */
$container = new YourContainer();
//
// do stuff
//


// 2. create a builder
$builder = \N1215\Jugoya\RequestHandlerBuilder::fromContainer($container);

// LazyRequestHandlerBuilder resolves handler and middleware lazily.
// $builder = \N1215\Jugoya\LazyRequestHandlerBuilder::fromContainer($container);

// 3. build a request handler
/**
 * You can use one of
 *   * an instance of PSR-15 RequestHandlerInterface
 *   * a callable having the same signature with PSR-15 RequestHandlerInterface
 *   * a string identifier of a PSR-15 RequestHandlerInterface instance in the PSR-11 Container
 *
 * @var RequestHandlerInterface|callable|string $coreHandler
 *
 */
$coreHandler = new YourApplication();

/** @var RequestHandlerInterface $handler */
$handler = $builder->build($coreHandler, [

        // You can use instances of PSR-15 MiddlewareInterface
        new YourMiddleware(),

        // or callables having the same signature with PSR-15 MiddlewareInterface
        function(ServerRequestInterface $request, RequestHandlerInterface $handler) {
            // do stuff
            $response = $handler->handle($request);
            // do stuff
            return $response;
        },

        // or string identifiers of PSR-15 MiddlewareInterface instances in the PSR-11 Container
        YourMiddleware::class,
    ]);


// 4. handle a PSR-7 Sever Request
/** @var Psr\Http\Message\ServerRequestInterface $request */
$request = \Zend\Diactoros\ServerRequestBuilder::fromGlobals();
/** @var \Psr\Http\Message\ResponseInterface $response */
$response = $handler->handle($request);
```

## Class diagrams

### Jugoya
![jugoya](doc/jugoya.png)

### Resolver
![resolver](doc/resolver.png)

### Wrapper
![wrapper](doc/wrapper.png)

## License

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.
