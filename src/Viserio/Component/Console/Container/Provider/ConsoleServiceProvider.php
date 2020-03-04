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

namespace Viserio\Component\Console\Container\Provider;

use Symfony\Component\Console\Application as SymfonyConsole;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Container\Pipeline\AddConsoleCommandPipe;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\Container\Pipeline\ResolvePreloadPipe;
use Viserio\Component\Container\PipelineConfig;
use Viserio\Contract\Container\ServiceProvider\AliasServiceProvider as AliasServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\ServiceProvider\PipelineServiceProvider as PipelineServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\ServiceProvider as ServiceProviderContract;
use Viserio\Contract\Events\EventManager as EventManagerContract;

class ConsoleServiceProvider implements AliasServiceProviderContract, PipelineServiceProviderContract, ServiceProviderContract
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilderContract $container): void
    {
        $container->singleton(Application::class)
            ->setArguments([
                '{viserio.console.version}',
                '{viserio.console.name}',
            ])
            ->addMethodCall('setContainer')
            ->addMethodCall('setEventManager', [new ReferenceDefinition(EventManagerContract::class, ReferenceDefinition::IGNORE_ON_INVALID_REFERENCE)])
            ->addMethodCall('setCommandLoader', [new ReferenceDefinition(CommandLoaderInterface::class, ReferenceDefinition::IGNORE_ON_INVALID_REFERENCE)])
            ->addTag(ResolvePreloadPipe::TAG)
            ->setPublic(true);
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias(): array
    {
        return [
            SymfonyConsole::class => Application::class,
            'console' => Application::class,
            'cerebro' => Application::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getPipelines(): array
    {
        return [
            PipelineConfig::TYPE_BEFORE_OPTIMIZATION => [
                [
                    new AddConsoleCommandPipe(),
                ],
            ],
        ];
    }
}
