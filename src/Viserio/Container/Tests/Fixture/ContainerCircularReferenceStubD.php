<?php
declare(strict_types=1);
namespace Viserio\Container\Tests\Fixture;

class ContainerCircularReferenceStubD
{
    public function __construct(ContainerCircularReferenceStubE $e)
    {
    }
}
