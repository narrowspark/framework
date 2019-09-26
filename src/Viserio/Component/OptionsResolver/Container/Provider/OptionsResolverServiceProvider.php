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

namespace Viserio\Component\OptionsResolver\Container\Provider;

use Viserio\Component\Console\Command\AbstractCommand;
use Viserio\Component\Container\PipelineConfig;
use Viserio\Component\OptionsResolver\Command\OptionDumpCommand;
use Viserio\Component\OptionsResolver\Command\OptionReaderCommand;
use Viserio\Component\OptionsResolver\Container\Pipeline\ResolveOptionDefinitionPipe;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\ServiceProvider\PipelineServiceProvider as PipelineServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\ServiceProvider as ServiceProviderContract;

class OptionsResolverServiceProvider implements PipelineServiceProviderContract,
    ServiceProviderContract
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilderContract $container): void
    {
        if (\class_exists(AbstractCommand::class)) {
            $container->singleton(OptionDumpCommand::class)
                ->addTag('console.command');
            $container->singleton(OptionReaderCommand::class)
                ->addTag('console.command');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getPipelines(): array
    {
        return [
            PipelineConfig::TYPE_BEFORE_OPTIMIZATION => [
                [
                    new ResolveOptionDefinitionPipe(),
                ],
            ],
        ];
    }
}
