<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Foundation\Bootstrap;

use Viserio\Component\Foundation\Tests\Helper\ClassStack;

function class_exists($class_name, $autoload = true)
{
    if (ClassStack::has($class_name)) {
        return ClassStack::get($class_name);
    }

    return \class_exists($class_name, $autoload);
}
