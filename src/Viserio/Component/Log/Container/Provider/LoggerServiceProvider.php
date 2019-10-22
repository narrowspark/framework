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

namespace Viserio\Component\Log\Container\Provider;

use Psr\Log\LoggerInterface;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\Log\LogManager;
use Viserio\Contract\Container\ServiceProvider\AliasServiceProvider as AliasServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\ServiceProvider\ServiceProvider as ServiceProviderContract;
use Viserio\Contract\Events\EventManager as EventManagerContract;

class LoggerServiceProvider implements AliasServiceProviderContract, ServiceProviderContract
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilderContract $container): void
    {
        $container->singleton(LogManager::class)
            ->addArgument(new ReferenceDefinition('config'))
            ->addMethodCall('setEventManager', [new ReferenceDefinition(EventManagerContract::class, ReferenceDefinition::IGNORE_ON_UNINITIALIZED_REFERENCE)])
            ->addTag('container.preload')
            ->setPublic(true);

        $container->singleton(LoggerInterface::class, [new ReferenceDefinition(LogManager::class), 'getDriver'])
            ->addTag('container.preload')
            ->setPublic(true);
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias(): array
    {
        return [
            'log' => LogManager::class,
        ];
    }
}
