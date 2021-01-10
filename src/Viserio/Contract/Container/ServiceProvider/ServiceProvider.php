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

use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;

interface ServiceProvider
{
    /**
     * Builds the service provider.
     *
     * It is only ever called once when the cache is empty.
     */
    public function build(ContainerBuilderContract $container): void;
}
