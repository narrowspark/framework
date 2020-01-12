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

namespace Viserio\Component\Config\Container\Provider;

use Viserio\Component\Config\Processor\Base64ParameterProcessor;
use Viserio\Component\Config\Processor\ConstantProcessor;
use Viserio\Component\Config\Processor\CsvParameterProcessor;
use Viserio\Component\Config\Processor\FileParameterProcessor;
use Viserio\Component\Config\Processor\JsonParameterProcessor;
use Viserio\Component\Config\Processor\PhpTypeParameterProcessor;
use Viserio\Component\Config\Processor\ResolveParameterProcessor;
use Viserio\Component\Config\Processor\UrlParameterProcessor;
use Viserio\Component\Config\Repository;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\Container\Pipeline\RegisterParameterProcessorsPipe;
use Viserio\Contract\Config\Repository as RepositoryContract;
use Viserio\Contract\Container\ServiceProvider\AliasServiceProvider as AliasServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\ServiceProvider\ServiceProvider as ServiceProviderContract;
use Viserio\Contract\Parser\Loader as LoaderContract;

class ConfigServiceProvider implements AliasServiceProviderContract,
    ServiceProviderContract
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

        $container->singleton(ResolveParameterProcessor::class)
            ->addArgument((new ReferenceDefinition(RepositoryContract::class))->addMethodCall('getAll'));

        $container->singleton(RepositoryContract::class, Repository::class)
            ->addMethodCall('setLoader', [new ReferenceDefinition(LoaderContract::class, ReferenceDefinition::IGNORE_ON_INVALID_REFERENCE)])
            ->addMethodCall('addParameterProcessor', [new ReferenceDefinition(Base64ParameterProcessor::class)])
            ->addMethodCall('addParameterProcessor', [new ReferenceDefinition(ConstantProcessor::class)])
            ->addMethodCall('addParameterProcessor', [new ReferenceDefinition(CsvParameterProcessor::class)])
            ->addMethodCall('addParameterProcessor', [new ReferenceDefinition(FileParameterProcessor::class)])
            ->addMethodCall('addParameterProcessor', [new ReferenceDefinition(JsonParameterProcessor::class)])
            ->addMethodCall('addParameterProcessor', [new ReferenceDefinition(PhpTypeParameterProcessor::class)])
            ->addMethodCall('addParameterProcessor', [new ReferenceDefinition(UrlParameterProcessor::class)])
            ->addMethodCall('addParameterProcessor', [new ReferenceDefinition(ResolveParameterProcessor::class)]);
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias(): array
    {
        return [
            Repository::class => RepositoryContract::class,
            'config' => [RepositoryContract::class, true],
        ];
    }
}
