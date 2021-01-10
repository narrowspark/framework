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
