<?php
namespace Viserio\Container\Tests\Fixture;

class ContainerTestContextInjectOneFixture
{
    public $impl;

    public function __construct(ContainerContractFixtureInterface $impl)
    {
        $this->impl = $impl;
    }
}
