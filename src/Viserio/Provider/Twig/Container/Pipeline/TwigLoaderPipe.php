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

namespace Viserio\Provider\Twig\Container\Pipeline;

use Twig\Environment as TwigEnvironment;
use Twig\Loader\ChainLoader;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Contract\Container\ContainerBuilder;
use Viserio\Contract\Container\Definition\ObjectDefinition;
use Viserio\Contract\Container\Pipe as PipeContract;

class TwigLoaderPipe implements PipeContract
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $containerBuilder): void
    {
        if ($containerBuilder->hasDefinition(TwigEnvironment::class) === false) {
            return;
        }

        $prioritizedLoaders = [];

        foreach ($containerBuilder->getTagged('twig.loader') as $loaders) {
            foreach ($loaders as $priority => $id) {
                $prioritizedLoaders[$priority][] = $id;
            }
        }

        if (\count($prioritizedLoaders) === 0) {
            return;
        }

        /** @var ObjectDefinition $chainLoaderDefinition */
        $chainLoaderDefinition = $containerBuilder->getDefinition(ChainLoader::class);

        \krsort($prioritizedLoaders);

        foreach ($prioritizedLoaders as $loaders) {
            foreach ($loaders as $loader) {
                $chainLoaderDefinition->addMethodCall('addLoader', [new ReferenceDefinition($loader)]);
            }
        }
    }
}
