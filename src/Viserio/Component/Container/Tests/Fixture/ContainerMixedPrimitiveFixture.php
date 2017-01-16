<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\Fixture;

class ContainerMixedPrimitiveFixture
{
    public $first;
    public $last;
    public $stub;

    public function __construct($first, ContainerConcreteFixture $stub, $last)
    {
        $this->stub  = $stub;
        $this->last  = $last;
        $this->first = $first;
    }
}
