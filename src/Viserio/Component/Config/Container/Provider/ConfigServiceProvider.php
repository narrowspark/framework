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

namespace Viserio\Component\Config\Container\Provider;

use Viserio\Component\Config\Command\ConfigDumpCommand;
use Viserio\Component\Config\Command\ConfigReaderCommand;
use Viserio\Component\Config\Container\Pipeline\ResolveConfigDefinitionPipe;
use Viserio\Component\Console\Command\AbstractCommand;
use Viserio\Component\Console\Container\Pipeline\AddConsoleCommandPipe;
use Viserio\Component\Container\PipelineConfig;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\ServiceProvider\PipelineServiceProvider as PipelineServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\ServiceProvider as ServiceProviderContract;

class ConfigServiceProvider implements PipelineServiceProviderContract,
    ServiceProviderContract
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilderContract $container): void
    {
        if (\class_exists(AbstractCommand::class)) {
            $container->singleton(ConfigDumpCommand::class)
                ->addTag(AddConsoleCommandPipe::TAG);
            $container->singleton(ConfigReaderCommand::class)
                ->addTag(AddConsoleCommandPipe::TAG);
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
                    new ResolveConfigDefinitionPipe(),
                ],
            ],
        ];
    }
}
