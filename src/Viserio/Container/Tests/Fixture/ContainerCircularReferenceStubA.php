<?php
namespace Viserio\Container\Tests\Fixture;

class ContainerCircularReferenceStubA
{
    public function __construct(ContainerCircularReferenceStubB $b)
    {
    }
}
