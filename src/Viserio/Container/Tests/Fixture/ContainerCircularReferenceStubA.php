<?php
declare(strict_types=1);
namespace Viserio\Container\Tests\Fixture;

class ContainerCircularReferenceStubA
{
    public function __construct(ContainerCircularReferenceStubB $b)
    {
    }
}
