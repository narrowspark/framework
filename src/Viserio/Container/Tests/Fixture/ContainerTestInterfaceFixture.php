<?php
namespace Viserio\Container\Tests\Fixture;

class ContainerTestInterfaceFixture
{
    public function __construct(ContainerContractFixtureInterface $stub)
    {
        $this->stub = $stub;
    }

    public function go(ContainerContractFixtureInterface $stub)
    {
        return $stub;
    }

    public function getStub()
    {
        return $this->stub;
    }
}
