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

namespace Viserio\Component\Cache\Container\Provider;

use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;
use Viserio\Component\Cache\CacheManager;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Contract\Cache\Manager as CacheManagerContract;
use Viserio\Contract\Container\ServiceProvider\AliasServiceProvider as AliasServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\ServiceProvider\ServiceProvider as ServiceProviderContract;

class CacheServiceProvider implements AliasServiceProviderContract, ServiceProviderContract
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilderContract $container): void
    {
        $container->singleton(CacheManagerContract::class, CacheManager::class)
            ->setArgument(0, new ReferenceDefinition('config'))
            ->addMethodCall('setContainer')
            ->addTag('container.preload');

        $container->singleton(
            CacheItemPoolInterface::class,
            [new ReferenceDefinition(CacheManagerContract::class), 'getDriver']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias(): array
    {
        return [
            CacheManager::class => CacheManagerContract::class,
            'cache' => CacheManagerContract::class,
            CacheInterface::class => CacheItemPoolInterface::class,
            'cache.store' => CacheItemPoolInterface::class,
        ];
    }
}
