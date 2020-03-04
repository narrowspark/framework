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

namespace Viserio\Component\Cache\Container\Provider;

use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;
use Viserio\Component\Cache\CacheManager;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\Container\Pipeline\ResolvePreloadPipe;
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
            ->addTag(ResolvePreloadPipe::TAG);

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
