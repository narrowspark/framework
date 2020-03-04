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

namespace Viserio\Contract\Profiler;

interface AssetAware extends DataCollector
{
    /**
     * Returns an array with the following keys:
     *  - css: an array of filenames
     *  - js: an array of filenames.
     */
    public function getAssets(): array;
}
