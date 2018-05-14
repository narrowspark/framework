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

use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;

interface ServiceProvider
{
    /**
     * Builds the service provider.
     *
     * It is only ever called once when the cache is empty.
     *
     * @param \Viserio\Contract\Container\ServiceProvider\ContainerBuilder $container
     *
     * @return void
     */
    public function build(ContainerBuilderContract $container): void;
}
