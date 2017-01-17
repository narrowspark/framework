<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\Fixture;

class ContainerCircularReferenceStubC
{
    public function __construct(ContainerCircularReferenceStubB $b)
    {
    }
}
