<?php
namespace Viserio\Container\Tests\Fixture;

class ContainerCircularReferenceStubB
{
    public function __construct(ContainerCircularReferenceStubC $c)
    {
    }
}
