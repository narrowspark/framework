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
