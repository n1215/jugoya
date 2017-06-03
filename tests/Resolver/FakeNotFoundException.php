<?php

namespace N1215\Jugoya\Resolver;

use Psr\Container\NotFoundExceptionInterface;

class FakeNotFoundException extends \Exception implements NotFoundExceptionInterface
{
}