<?php
namespace Viserio\Container\Tests\Fixture;

class ContainerCircularReferenceStubD
{
    public function __construct(ContainerCircularReferenceStubE $e)
    {
    }
}
