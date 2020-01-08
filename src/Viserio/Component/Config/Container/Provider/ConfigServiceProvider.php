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

use Viserio\Bridge\Dotenv\Env;
use Viserio\Component\Config\Container\Pipeline\ResolveOptionDefinitionPipe;
use Viserio\Component\Config\Container\Pipeline\ResolveParameterPipe;
use Viserio\Component\Config\ParameterProcessor\EnvParameterProcessor;
use Viserio\Component\Config\Repository;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\Container\PipelineConfig;
use Viserio\Contract\Config\Repository as RepositoryContract;
use Viserio\Contract\Container\ServiceProvider\AliasServiceProvider as AliasServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\ServiceProvider\PipelineServiceProvider as PipelineServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\ServiceProvider as ServiceProviderContract;
use Viserio\Contract\Parser\Loader as LoaderContract;

class ConfigServiceProvider implements AliasServiceProviderContract,
    PipelineServiceProviderContract,
    ServiceProviderContract
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilderContract $container): void
    {
        $definition = $container->singleton(RepositoryContract::class, Repository::class)
            ->addMethodCall('setLoader', [new ReferenceDefinition(LoaderContract::class, ReferenceDefinition::IGNORE_ON_INVALID_REFERENCE)]);

        if (class_exists(Env::class)) {
            $definition->addMethodCall('addParameterProcessor', [new EnvParameterProcessor()]);
        }
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

    /**
     * {@inheritdoc}
     */
    public function getPipelines(): array
    {
        return [
            PipelineConfig::TYPE_BEFORE_OPTIMIZATION => [
                64 => [
                    new ResolveOptionDefinitionPipe(),
                    new ResolveParameterPipe(),
                ],
            ],
        ];
    }
}
