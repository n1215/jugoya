# Jugoya（十五夜）🌕
A PSR-15 HTTP application builder.

[![Latest Stable Version](https://poser.pugx.org/n1215/jugoya/v/stable)](https://packagist.org/packages/n1215/jugoya)
[![License](https://poser.pugx.org/n1215/jugoya/license)](https://packagist.org/packages/n1215/jugoya)
[![Build Status](https://scrutinizer-ci.com/g/n1215/jugoya/badges/build.png?b=master)](https://scrutinizer-ci.com/g/n1215/jugoya/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/n1215/jugoya/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/n1215/jugoya/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/n1215/jugoya/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/n1215/jugoya/?branch=master)

# Example

```php
// 1. register middleware dependencies to the PSR-11 Container
/** @var \Psr\Container\ContainerInterface $container */
$container = new YourContainer();
//
// do stuff
//


// 2. create an application builder
$builder = \N1215\Jugoya\Jugoya::fromContainer($container);

// 3. build an http application
/** @var DelegateInterface|callable|string $coreDelegate */
$coreDelegate = new YourApplication();

/** @var HttpApplication|DelegateInterface $app */
$app = $builder->build($coreDelegate, [

        // a callable having the same signature with PSR-15 MiddlewareInterface
        function(ServerRequestInterface $request, DelegateInterface $delegate) {
            // do stuff
            $response = $delegate->process($request);
            // do stuff
            return $response;
        },

        // an instance of PSR-15 MiddlewareInterface
        new YourMiddleware(),

        // a key string for an instance of PSR-15 MiddlewareInterface in the PSR-11 Container
        YourMiddleware::class,
    ]);


// 4. handle a PSR-7 Sever Request
/** @var Psr\Http\Message\ServerRequestInterface $request */
$request = \Zend\Diactoros\ServerRequestFactory::fromGlobals();
/** @var \Psr\Http\Message\ResponseInterface $response */
$response = $app->process($request);
```

# License

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.