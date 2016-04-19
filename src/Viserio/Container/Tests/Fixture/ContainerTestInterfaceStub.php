<?php
namespace Viserio\Container\Tests\Fixture;

class ContainerTestInterfaceStub
{
    public function __construct(IContainerContractStub $stub)
    {
        $this->stub = $stub;
    }

    public function go(IContainerContractStub $stub)
    {
        return $stub;
    }

    public function getStub()
    {
        return $this->stub;
    }
}
