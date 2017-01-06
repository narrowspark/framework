<?php
declare(strict_types=1);
namespace Viserio\Container\Tests\Fixture;

class ContainerTestContextInjectInstantiationsFixture implements ContainerContractFixtureInterface
{
    public static $instantiations;

    public function __construct()
    {
        static::$instantiations++;
    }
}
