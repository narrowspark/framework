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

namespace Viserio\Component\Foundation\Container\Provider;

use Psr\Container\ContainerInterface;
use Viserio\Component\Config\ParameterProcessor\ComposerExtraProcessor;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\Foundation\Config\Processor\DirectoryProcessor;
use Viserio\Contract\Config\Repository as RepositoryContract;
use Viserio\Contract\Container\Definition\ObjectDefinition as ObjectDefinitionContract;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\ServiceProvider\ExtendServiceProvider as ExtendServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\ServiceProvider as ServiceProviderContract;
use Viserio\Contract\Foundation\Kernel as KernelContract;

class ConfigServiceProvider implements ExtendServiceProviderContract, ServiceProviderContract
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilderContract $container): void
    {
        $container->singleton(DirectoryProcessor::class)
            ->setArguments([
                new ReferenceDefinition('config'),
                new ReferenceDefinition(ContainerInterface::class),
            ]);
        $container->singleton(ComposerExtraProcessor::class)
            ->setArguments([
                (new ReferenceDefinition(KernelContract::class))
                    ->addMethodCall('getRootDir'),
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions(): array
    {
        return [
            RepositoryContract::class => static function (ObjectDefinitionContract $definition): void {
                $definition->addMethodCall('addParameterProcessor', [new ReferenceDefinition(ComposerExtraProcessor::class)]);
                $definition->addMethodCall('addParameterProcessor', [new ReferenceDefinition(DirectoryProcessor::class)]);

                $definition->addMethodCall('set', ['viserio.app.env', (new ReferenceDefinition(KernelContract::class))->addMethodCall('getEnvironment')]);
                $definition->addMethodCall('set', ['viserio.app.debug', (new ReferenceDefinition(KernelContract::class))->addMethodCall('isDebug')]);

                $definition->addTag('container.preload');
            },
        ];
    }
}
