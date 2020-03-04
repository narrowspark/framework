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

namespace Viserio\Component\Log\Container\Provider;

use Psr\Log\LoggerInterface;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\Container\Pipeline\ResolvePreloadPipe;
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
            ->addTag(ResolvePreloadPipe::TAG)
            ->setPublic(true);

        $container->singleton(LoggerInterface::class, [new ReferenceDefinition(LogManager::class), 'getDriver'])
            ->addTag(ResolvePreloadPipe::TAG)
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
