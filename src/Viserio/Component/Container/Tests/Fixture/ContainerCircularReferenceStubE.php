<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\Fixture;

class ContainerCircularReferenceStubE
{
    public function __construct(ContainerCircularReferenceStubF $f)
    {
    }
}
