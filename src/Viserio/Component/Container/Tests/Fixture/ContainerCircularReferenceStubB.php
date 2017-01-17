<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\Fixture;

class ContainerCircularReferenceStubB
{
    public function __construct(ContainerCircularReferenceStubC $c)
    {
    }
}
