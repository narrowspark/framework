<?php
declare(strict_types=1);
namespace Viserio\Container\Tests\Fixture;

class ContainerInjectVariableFixture
{
    public $something;

    public function __construct(ContainerConcreteFixture $concrete, $something)
    {
        $this->something = $something;
    }

    public function set(ContainerConcreteFixture $concrete)
    {
        $this->something = $something;
    }
}
