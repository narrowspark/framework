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

interface PanelAware extends DataCollector
{
    /**
     * Returns all data in a panel window.
     *
     * @return string
     */
    public function getPanel(): string;
}
