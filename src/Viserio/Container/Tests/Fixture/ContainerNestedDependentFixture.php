<?php
namespace Viserio\Container\Tests\Fixture;

class ContainerNestedDependentFixture
{
    public $inner;

    public function __construct(ContainerDependentFixture $inner)
    {
        $this->inner = $inner;
    }
}
