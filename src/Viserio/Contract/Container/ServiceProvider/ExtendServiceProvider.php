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
     *     function (\Viserio\Contract\Container\Definition $definition, Viserio\Contract\Container\ServiceProvider\ContainerBuilder $container): \Viserio\Contract\Container\Definition
     *
     * @return callable[]
     */
    public function getExtensions(): array;
}
