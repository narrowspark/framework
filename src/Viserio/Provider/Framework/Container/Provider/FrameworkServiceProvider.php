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

namespace Viserio\Provider\Debug\Container\Provider;

use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\Container\Pipeline\RegisterParameterProcessorsPipe;
use Viserio\Component\Container\Processor\Base64ParameterProcessor;
use Viserio\Component\Container\Processor\ConstantProcessor;
use Viserio\Component\Container\Processor\CsvParameterProcessor;
use Viserio\Component\Container\Processor\FileParameterProcessor;
use Viserio\Component\Container\Processor\JsonParameterProcessor;
use Viserio\Component\Container\Processor\PhpTypeParameterProcessor;
use Viserio\Component\Container\Processor\UrlParameterProcessor;
use Viserio\Component\Foundation\Config\Processor\DirectoryParameterProcessor;
use Viserio\Contract\Container\CompiledContainer as CompiledContainerContract;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\ServiceProvider\ServiceProvider as ServiceProviderContract;
use Viserio\Contract\Foundation\Kernel as KernelContract;

class FrameworkServiceProvider implements ServiceProviderContract
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilderContract $container): void
    {
        $container->singleton(Base64ParameterProcessor::class)
            ->addTag(RegisterParameterProcessorsPipe::TAG);
        $container->singleton(ConstantProcessor::class)
            ->addTag(RegisterParameterProcessorsPipe::TAG);
        $container->singleton(CsvParameterProcessor::class)
            ->addTag(RegisterParameterProcessorsPipe::TAG);
        $container->singleton(FileParameterProcessor::class)
            ->addTag(RegisterParameterProcessorsPipe::TAG);
        $container->singleton(JsonParameterProcessor::class)
            ->addTag(RegisterParameterProcessorsPipe::TAG);
        $container->singleton(PhpTypeParameterProcessor::class)
            ->addTag(RegisterParameterProcessorsPipe::TAG);
        $container->singleton(UrlParameterProcessor::class)
            ->addTag(RegisterParameterProcessorsPipe::TAG);

        $container->singleton(DirectoryParameterProcessor::class)
            ->setArguments([
                '{app.framework.directories}',
                new ReferenceDefinition(CompiledContainerContract::class),
            ])
            ->addTag(RegisterParameterProcessorsPipe::TAG);

        $kernelRef = new ReferenceDefinition(KernelContract::class);

        $container->setParameter('viserio.app.env', $kernelRef->addMethodCall('getEnvironment'));
        $container->setParameter('viserio.app.debug', $kernelRef->addMethodCall('isDebug'));
    }
}
