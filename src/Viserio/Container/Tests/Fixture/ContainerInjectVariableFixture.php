<?php
namespace Viserio\Container\Tests\Fixture;

class ContainerInjectVariableFixture
{
    public $something;

    public function __construct(ContainerConcreteFixture $concrete, $something)
    {
        $this->something = $something;
    }
}
