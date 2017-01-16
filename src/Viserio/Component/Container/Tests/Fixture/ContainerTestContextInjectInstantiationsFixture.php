<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\Fixture;

class ContainerTestContextInjectInstantiationsFixture implements ContainerContractFixtureInterface
{
    public static $instantiations;

    public function __construct()
    {
        static::$instantiations++;
    }
}
