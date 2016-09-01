<?php
declare(strict_types=1);
namespace Viserio\Container\Tests\Fixture;

class ContainerCircularReferenceStubE
{
    public function __construct(ContainerCircularReferenceStubF $f)
    {
    }
}
