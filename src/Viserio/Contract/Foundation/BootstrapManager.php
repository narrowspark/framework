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

interface BootstrapManager
{
    /**
     * Determine if the application has been bootstrapped before.
     */
    public function hasBeenBootstrapped(): bool;

    /**
     * Register a callback to run before a bootstrapper.
     */
    public function addBeforeBootstrapping(string $bootstrapper, callable $callback): void;

    /**
     * Register a callback to run after a bootstrapper.
     */
    public function addAfterBootstrapping(string $bootstrapper, callable $callback): void;

    /**
     * Run the given array of bootstrap classes.
     */
    public function bootstrapWith(array $bootstraps): void;
}
