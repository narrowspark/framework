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

namespace Viserio\Contract\Profiler;

interface AssetAware extends DataCollector
{
    /**
     * Returns an array with the following keys:
     *  - css: an array of filenames
     *  - js: an array of filenames.
     *
     * @return array
     */
    public function getAssets(): array;
}
