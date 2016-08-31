<?php
namespace Viserio\Container\Tests\Fixture;

class ContainerTestContextInjectTwoFixture
{
    public $impl;

    public function __construct(ContainerContractFixtureInterface $impl)
    {
        $this->impl = $impl;
    }
}
