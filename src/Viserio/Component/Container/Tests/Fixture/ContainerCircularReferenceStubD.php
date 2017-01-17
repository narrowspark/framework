<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\Fixture;

class ContainerCircularReferenceStubD
{
    public function __construct(ContainerCircularReferenceStubE $e)
    {
    }
}
