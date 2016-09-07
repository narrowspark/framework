<?php
declare(strict_types=1);
namespace Viserio\Container\Tests\Fixture;

class ContainerDependentFixture
{
    public $impl;

    public function __construct(ContainerContractFixtureInterface $impl)
    {
        $this->impl = $impl;
    }
}
