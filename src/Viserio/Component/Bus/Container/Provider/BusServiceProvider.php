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

namespace Viserio\Component\Bus\Container\Provider;

use Viserio\Component\Bus\Dispatcher;
use Viserio\Contract\Bus\Dispatcher as DispatcherContract;
use Viserio\Contract\Container\ServiceProvider\AliasServiceProvider as AliasServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\ServiceProvider\ServiceProvider as ServiceProviderContract;

class BusServiceProvider implements AliasServiceProviderContract, ServiceProviderContract
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilderContract $container): void
    {
        $container->singleton(DispatcherContract::class, Dispatcher::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias(): array
    {
        return [
            Dispatcher::class => DispatcherContract::class,
            'bus' => DispatcherContract::class,
        ];
    }
}
