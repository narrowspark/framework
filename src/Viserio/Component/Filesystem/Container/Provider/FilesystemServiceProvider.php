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

namespace Viserio\Component\Filesystem\Container\Provider;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\Filesystem\Cache\CachedFactory;
use Viserio\Component\Filesystem\FilesystemManager;
use Viserio\Contract\Cache\Manager as CacheManagerContract;
use Viserio\Contract\Container\ServiceProvider\AliasServiceProvider as AliasServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\ServiceProvider\ServiceProvider as ServiceProviderContract;

class FilesystemServiceProvider implements AliasServiceProviderContract, ServiceProviderContract
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilderContract $container): void
    {
        $container->singleton(FilesystemManager::class)
            ->setArguments([new ReferenceDefinition('config')])
            ->addMethodCall('setCacheManager', [
                new ReferenceDefinition(CacheManagerContract::class, ReferenceDefinition::IGNORE_ON_INVALID_REFERENCE),
            ]);

        $container->singleton('flysystem.connection', [new ReferenceDefinition(FilesystemManager::class), 'getConnection']);

        $container->singleton(CachedFactory::class)
            ->setArguments([
                new ReferenceDefinition(FilesystemManager::class),
                new ReferenceDefinition(CacheManagerContract::class, ReferenceDefinition::NULL_ON_INVALID_REFERENCE),
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias(): array
    {
        return [
            'flysystem' => FilesystemManager::class,
            Filesystem::class => FilesystemManager::class,
            FilesystemInterface::class => FilesystemManager::class,
            'flysystem.cached.factory' => CachedFactory::class,
        ];
    }
}
