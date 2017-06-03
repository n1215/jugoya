<?php

namespace N1215\Jugoya\Resolver;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Container\ContainerExceptionInterface;

trait ResolverTrait
{

    /**
     * @param string $ref
     * @param ContainerInterface $container
     * @param string $expectedClass
     * @return mixed
     * @throws UnresolvedException
     */
    protected function resolveByContainer($ref, ContainerInterface $container, $expectedClass)
    {
        try {
            $entry = $container->get($ref);
        } catch (NotFoundExceptionInterface $e) {
            throw new UnresolvedException('Could not found an entry from the container.', 0, $e);
        } catch (ContainerExceptionInterface $e) {
            throw new UnresolvedException('Something wrong with the container.', 0, $e);
        }

        $this->assertInstanceOf($expectedClass, $entry);

        return $entry;
    }

    /**
     * @param string $expectedClass
     * @param mixed $entry
     * @return void
     * @throws UnresolvedException
     */
    private function assertInstanceOf($expectedClass, $entry)
    {
        if ($entry instanceof $expectedClass) {
            return;
        }
        $type = is_object($entry) ? get_class($entry) : gettype($entry);
        throw new UnresolvedException("Expected container returns an instance of {$expectedClass}, {$type} given.");
    }
}
