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

namespace Viserio\Component\Parser\Container\Provider;

use Symfony\Component\Yaml\Yaml;
use Viserio\Component\Console\Container\Pipeline\AddConsoleCommandPipe;
use Viserio\Component\Parser\Command\XliffLintCommand;
use Viserio\Component\Parser\Command\YamlLintCommand;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\ServiceProvider\ServiceProvider as ServiceProviderContract;

class ConsoleCommandsServiceProvider implements ServiceProviderContract
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilderContract $container): void
    {
        $container->singleton(XliffLintCommand::class)
            ->addTag(AddConsoleCommandPipe::TAG);

        if (\class_exists(Yaml::class)) {
            $container->singleton(YamlLintCommand::class)
                ->addTag(AddConsoleCommandPipe::TAG);
        }
    }
}
