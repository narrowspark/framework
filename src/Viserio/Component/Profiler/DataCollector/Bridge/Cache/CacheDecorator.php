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

namespace Viserio\Component\Profiler\DataCollector\Bridge\Cache;

interface CacheDecorator
{
    /**
     * Get the original class name.
     */
    public function getName(): string;

    /**
     * Get a list of calls.
     */
    public function getCalls(): array;
}
