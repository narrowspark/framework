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

namespace Viserio\Component\Container\Dumper;

function dirname($path, $levels = 1): string
{
    if (\strpos($path, 'autoload_real.php') !== false) {
        $dir = \dirname(__DIR__, 1);

        if (\stripos($dir, 'container') !== false) {
            return $dir . '/vendor';
        }
    }

    return \dirname($path, $levels);
}
