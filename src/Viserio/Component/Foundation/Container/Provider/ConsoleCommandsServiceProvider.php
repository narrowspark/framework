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

use Viserio\Component\Config\Command\ConfigCacheCommand as BaseConfigCacheCommand;
use Viserio\Component\Config\Command\ConfigClearCommand as BaseConfigClearCommand;
use Viserio\Component\Foundation\Config\Command\ConfigCacheCommand;
use Viserio\Component\Foundation\Config\Command\ConfigClearCommand;
use Viserio\Contract\Container\Definition\ObjectDefinition as ObjectDefinitionContract;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\ServiceProvider\ExtendServiceProvider as ExtendServiceProviderContract;

class ConsoleCommandsServiceProvider implements ExtendServiceProviderContract
{
    /**
     * {@inheritdoc}
     */
    public function getExtensions(): array
    {
        return [
            BaseConfigCacheCommand::class => static function (ObjectDefinitionContract $definition, ContainerBuilderContract $container): void {
                $container->singleton(BaseConfigCacheCommand::class, ConfigCacheCommand::class)
                    ->addTag('console.command');

                $container->setAlias(BaseConfigCacheCommand::class, ConfigCacheCommand::class);
            },
            BaseConfigClearCommand::class => static function (ObjectDefinitionContract $definition, ContainerBuilderContract $container): void {
                $container->singleton(BaseConfigClearCommand::class, ConfigClearCommand::class)
                    ->addTag('console.command');

                $container->setAlias(BaseConfigClearCommand::class, ConfigClearCommand::class);
            },
        ];
    }
}
