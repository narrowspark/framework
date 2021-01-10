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
