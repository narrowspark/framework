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

namespace Viserio\Component\Profiler\DataCollector\Bridge\Cache;

interface CacheDecorator
{
    /**
     * Get the original class name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get a list of calls.
     *
     * @return array
     */
    public function getCalls(): array;
}
