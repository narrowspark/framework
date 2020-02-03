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

namespace Viserio\Provider\Framework\Container\Provider;

use Viserio\Component\Config\Container\Definition\ConfigDefinition;
use Viserio\Component\Console\Container\Pipeline\AddConsoleCommandPipe;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\Container\Pipeline\RegisterParameterProcessorsPipe;
use Viserio\Component\Container\Pipeline\ResolvePreloadPipe;
use Viserio\Component\Container\Pipeline\UnusedTagsPipe;
use Viserio\Component\Container\Processor\Base64ParameterProcessor;
use Viserio\Component\Container\Processor\ConstantProcessor;
use Viserio\Component\Container\Processor\CsvParameterProcessor;
use Viserio\Component\Container\Processor\EnvParameterProcessor;
use Viserio\Component\Container\Processor\FileParameterProcessor;
use Viserio\Component\Container\Processor\JsonParameterProcessor;
use Viserio\Component\Container\Processor\PhpTypeParameterProcessor;
use Viserio\Component\Container\Processor\UrlParameterProcessor;
use Viserio\Contract\Container\CompiledContainer as CompiledContainerContract;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\ServiceProvider\PipelineServiceProvider as PipelineServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\ServiceProvider as ServiceProviderContract;
use Viserio\Provider\Framework\Container\Processor\DirectoryParameterProcessor;

class FrameworkServiceProvider implements PipelineServiceProviderContract, ServiceProviderContract
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
                new ConfigDefinition('mapper', DirectoryParameterProcessor::class),
                new ReferenceDefinition(CompiledContainerContract::class),
            ])
            ->addTag(RegisterParameterProcessorsPipe::TAG);

        $container->singleton(EnvParameterProcessor::class)
            ->addTag(RegisterParameterProcessorsPipe::TAG);
    }

    /**
     * {@inheritdoc}
     */
    public function getPipelines(): array
    {
        return [
            'afterRemoving' => [
                [
                    new UnusedTagsPipe([
                        AddConsoleCommandPipe::TAG,
                        ResolvePreloadPipe::TAG,
                        'monolog.logger',
                        'proxy',
                        'translation.dumper',
                        'translation.extractor',
                        'translation.loader',
                        'twig.extension',
                        'twig.loader',
                    ]),
                ],
            ],
        ];
    }
}
