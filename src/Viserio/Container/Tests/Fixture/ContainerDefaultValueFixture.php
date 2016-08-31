<?php
namespace Viserio\Container\Tests\Fixture;

class ContainerDefaultValueFixture
{
    public $stub;
    public $default;

    public function __construct(ContainerConcreteFixture $stub, $default = 'narrowspark')
    {
        $this->stub = $stub;
        $this->default = $default;
    }
}
