<?php
namespace Viserio\Container\Tests\Fixture;

class ContainerCircularReferenceStubF
{
    public function __construct(ContainerCircularReferenceStubD $d)
    {
    }
}
