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

interface BootstrapManager
{
    /**
     * Determine if the application has been bootstrapped before.
     *
     * @return bool
     */
    public function hasBeenBootstrapped(): bool;

    /**
     * Register a callback to run before a bootstrapper.
     *
     * @param string   $bootstrapper
     * @param callable $callback
     *
     * @return void
     */
    public function addBeforeBootstrapping(string $bootstrapper, callable $callback): void;

    /**
     * Register a callback to run after a bootstrapper.
     *
     * @param string   $bootstrapper
     * @param callable $callback
     *
     * @return void
     */
    public function addAfterBootstrapping(string $bootstrapper, callable $callback): void;

    /**
     * Run the given array of bootstrap classes.
     *
     * @param array $bootstraps
     *
     * @return void
     */
    public function bootstrapWith(array $bootstraps): void;
}
