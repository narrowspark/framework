<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\Fixture;

class ContainerCircularReferenceStubA
{
    public function __construct(ContainerCircularReferenceStubB $b)
    {
    }
}
