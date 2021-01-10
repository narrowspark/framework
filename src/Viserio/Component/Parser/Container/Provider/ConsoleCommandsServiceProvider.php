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
