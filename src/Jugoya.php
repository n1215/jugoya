<?php

namespace N1215\Jugoya;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;

class Jugoya
{

    /**
     * @var MiddlewareResolverInterface
     */
    private $middlewareResolver;

    /**
     * @var DelegateInterface|callable|null
     */
    private $coreDelegate = null;

    /**
     * @var MiddlewareInterface[]|callable[]|string[]
     */
    private $middlewareEntries = [];

    /**
     * @param MiddlewareResolverInterface $resolver
     */
    public function __construct(MiddlewareResolverInterface $resolver)
    {
        $this->middlewareResolver = $resolver;
    }

    /**
     * @param DelegateInterface|callable $coreDelegate
     * @return Jugoya
     */
    public function from($coreDelegate)
    {
        $this->coreDelegate = $coreDelegate;
        return $this;
    }

    /**
     * @param MiddlewareInterface[]|callable[]|string[] $entries
     * @return Jugoya
     */
    public function middleware(array $entries)
    {
        foreach ($entries as $entry) {
            $this->middlewareEntries[] = $entry;
        }
        return $this;
    }

    /**
     * @return HttpApplication
     */
    public function build()
    {
        if (is_null($this->coreDelegate)) {
            throw new \LogicException('Please call Jugoya::from() and set a core delegate before build an HTTP Application.');
        }

        $coreDelegate = $this->resolveDelegate($this->coreDelegate);

        /**
         * @var MiddlewareInterface[] $middlewareQueue
         */
        $middlewareQueue = array_map(function($entry) {
            return $this->middlewareResolver->resolve($entry);
        }, $this->middlewareEntries);

        return new HttpApplication($coreDelegate, new MiddlewarePipeline($middlewareQueue));
    }

    /**
     * @param DelegateInterface|callable $delegate
     * @return DelegateInterface
     */
    private function resolveDelegate($delegate)
    {
        if ($delegate instanceof DelegateInterface) {
            return $delegate;
        }

        if (is_callable($delegate)) {
                return new CallableDelegate($delegate);
        }

        throw new \LogicException('$delegate must be one of an DelegateInterface or a callable');
    }

}
