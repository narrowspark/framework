<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\Fixture;

class ContainerNestedDependentFixture
{
    public $inner;

    public function __construct(ContainerDependentFixture $inner)
    {
        $this->inner = $inner;
    }
}
