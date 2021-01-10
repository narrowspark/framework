<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
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
