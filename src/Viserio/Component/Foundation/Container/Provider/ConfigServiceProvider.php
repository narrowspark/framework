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
use Viserio\Component\Container\PipelineConfig;
use Viserio\Component\Foundation\Config\Processor\DirectoryProcessor;
use Viserio\Component\Foundation\Container\Pipeline\ResolveParameterPipe;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\ServiceProvider\PipelineServiceProvider as PipelineServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\ServiceProvider as ServiceProviderContract;
use Viserio\Contract\Foundation\Kernel as KernelContract;

class ConfigServiceProvider implements PipelineServiceProviderContract, ServiceProviderContract
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilderContract $container): void
    {
        $container->singleton(DirectoryProcessor::class)
            ->setArguments([
                (new ReferenceDefinition(ContainerBuilderContract::class))
                    ->addMethodCall('getParameters'),
                new ReferenceDefinition(ContainerInterface::class),
            ]);
        $container->singleton(ComposerExtraProcessor::class)
            ->setArguments([
                (new ReferenceDefinition(KernelContract::class))
                    ->addMethodCall('getRootDir'),
            ]);

        $container->setParameter('viserio.app.env', (new ReferenceDefinition(KernelContract::class))->addMethodCall('getEnvironment'));
        $container->setParameter('viserio.app.debug', (new ReferenceDefinition(KernelContract::class))->addMethodCall('isDebug'));
    }

    /**
     * {@inheritdoc}
     */
    public function getPipelines(): array
    {
        return [
            PipelineConfig::TYPE_BEFORE_OPTIMIZATION => [
                64 => [
                    new ResolveParameterPipe(),
                ],
            ],
        ];
    }
}
