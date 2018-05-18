<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\Fixture\Util;

class ExtendReflectionGetParameterTypeClass extends ReflectionGetParameterTypeClass
{
    public function methodExt(parent $parent): void
    {
    }
}
