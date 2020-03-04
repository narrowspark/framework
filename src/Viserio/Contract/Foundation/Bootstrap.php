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

namespace Viserio\Contract\Foundation;

interface Bootstrap
{
    /**
     * Returns the bootstrap priority.
     */
    public static function getPriority(): int;

    /**
     * Bootstrap the given kernel.
     *
     * @param \Viserio\Contract\Foundation\Kernel $kernel
     */
    public static function bootstrap(Kernel $kernel): void;

    /**
     * Check when a bootstrap needs to run.
     *
     * @param \Viserio\Contract\Foundation\Kernel $kernel
     */
    public static function isSupported(Kernel $kernel): bool;
}
