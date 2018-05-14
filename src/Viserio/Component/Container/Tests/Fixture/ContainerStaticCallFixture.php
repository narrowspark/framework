<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\Fixture;

class ContainerStaticCallFixture
{
    public static function __callStatic($method, $parameters)
    {
        return \call_user_func_array([new TestClass($method, __CLASS__), $method], $parameters);
    }
}
