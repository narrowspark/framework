<?php
namespace Viserio\Container\Tests\Fixture;

class ContainerCircularReferenceStubE
{
    public function __construct(ContainerCircularReferenceStubF $f)
    {
    }
}
