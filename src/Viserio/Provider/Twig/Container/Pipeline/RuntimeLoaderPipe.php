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

use Twig\RuntimeLoader\RuntimeLoaderInterface;
use Viserio\Component\Container\Argument\IteratorArgument;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Contract\Container\ContainerBuilder;
use Viserio\Contract\Container\Definition\ObjectDefinition as ObjectDefinitionContract;
use Viserio\Contract\Container\Pipe as PipeContract;
use Viserio\Provider\Twig\RuntimeLoader\IteratorRuntimeLoader;

class RuntimeLoaderPipe implements PipeContract
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $containerBuilder): void
    {
        $mapping = [];

        foreach ($containerBuilder->getTagged('twig.runtime') as $definition) {
            if (! $definition instanceof ObjectDefinitionContract) {
                continue;
            }

            $id = $definition->getName();
            $class = $definition->getClass();

            $mapping[$id] = (new ReferenceDefinition($id))->setType($class);
        }

        $containerBuilder->singleton(RuntimeLoaderInterface::class, IteratorRuntimeLoader::class)
            ->addArgument(new IteratorArgument($mapping))
            ->setPublic(true);
    }
}
