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
            throw new UnresolvedException(
                "Could not found an entry identified by '{$ref}' from the container.",
                ErrorCode::NOT_FOUND,
                $e
            );
        } catch (ContainerExceptionInterface $e) {
            throw new UnresolvedException(
                "Something went wrong with the container when trying to get an entry identified by '{$ref}'.",
                ErrorCode::CONTAINER_ERROR,
                $e
            );
        }

        $this->assertInstanceOf($expectedClass, $entry, $ref);

        return $entry;
    }

    /**
     * @param string $expectedClass
     * @param mixed $entry
     * @param string $ref
     * @return void
     * @throws UnresolvedException
     */
    private function assertInstanceOf($expectedClass, $entry, $ref)
    {
        if ($entry instanceof $expectedClass) {
            return;
        }

        $type = is_object($entry) ? get_class($entry) : gettype($entry);
        throw new UnresolvedException(
            "Expected container returns an instance of {$expectedClass}, {$type} given. id='{$ref}'.",
            ErrorCode::TYPE_ERROR
        );
    }
}
