<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\Fixture;

class ContainerCircularReferenceStubF
{
    public function __construct(ContainerCircularReferenceStubD $d)
    {
    }
}
