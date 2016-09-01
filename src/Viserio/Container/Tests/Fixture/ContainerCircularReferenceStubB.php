<?php
declare(strict_types=1);
namespace Viserio\Container\Tests\Fixture;

class ContainerCircularReferenceStubB
{
    public function __construct(ContainerCircularReferenceStubC $c)
    {
    }
}
