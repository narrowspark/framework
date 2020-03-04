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

interface PanelAware extends DataCollector
{
    /**
     * Returns all data in a panel window.
     */
    public function getPanel(): string;
}
