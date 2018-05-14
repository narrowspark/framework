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

namespace Viserio\Component\Profiler\Tests\Fixture;

use Viserio\Component\Profiler\Profiler;

class ProfilerTester extends Profiler
{
    /**
     * Determine if we are running in the console.
     *
     * @return bool
     *
     * @codeCoverageIgnore
     */
    protected function runningInConsole(): bool
    {
        return false;
    }
}
