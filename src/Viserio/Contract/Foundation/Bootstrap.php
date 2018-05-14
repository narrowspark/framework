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

namespace Viserio\Contract\Foundation;

interface Bootstrap
{
    /**
     * Returns the bootstrap priority.
     *
     * @return int
     */
    public static function getPriority(): int;

    /**
     * Bootstrap the given kernel.
     *
     * @param \Viserio\Contract\Foundation\Kernel $kernel
     *
     * @return void
     */
    public static function bootstrap(Kernel $kernel): void;
}
