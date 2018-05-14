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
