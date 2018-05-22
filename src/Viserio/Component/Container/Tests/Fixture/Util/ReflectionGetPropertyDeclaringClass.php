<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\Fixture\Util;

use Viserio\Component\Container\Tests\Fixture\Util\Traits\TraitB;
use Viserio\Component\Container\Tests\Fixture\Util\Traits\TraitC;

class ReflectionGetPropertyDeclaringClass
{
    use TraitB;
    use TraitC;

    protected $own;
}
