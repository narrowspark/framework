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

namespace Viserio\Contract\Container\ServiceProvider;

interface ExtendServiceProvider
{
    /**
     * Returns a list of all container entries extended by this service provider.
     *
     * - the key is the entry name
     * - the value is a callable that will return the modified entry
     *
     * Callable have the following signature:
     *     function (\Viserio\Contract\Container\Definition $definition): \Viserio\Contract\Container\Definition,
     *     function (\Viserio\Contract\Container\Definition $definition, Viserio\Contract\Container\ContainerBuilder $container): \Viserio\Contract\Container\Definition
     *
     * @return callable[]
     */
    public function getExtensions(): array;
}
