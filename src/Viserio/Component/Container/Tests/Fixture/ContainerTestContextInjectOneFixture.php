<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\Fixture;

class ContainerTestContextInjectOneFixture
{
    public $impl;

    public function __construct(ContainerContractFixtureInterface $impl)
    {
        $this->impl = $impl;
    }
}
