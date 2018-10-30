<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Bootstrap;

use Viserio\Component\Foundation\Tests\Helper\ClassStack;

function class_exists($class_name, $autoload = true)
{
    if (ClassStack::has($class_name)) {
        return ClassStack::get($class_name);
    }

    return \class_exists($class_name, $autoload);
}
