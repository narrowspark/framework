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

namespace Viserio\Component\Container\Pipeline;

use SplPriorityQueue;
use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\Definition\DecoratorAwareDefinition as DecoratorAwareDefinitionContract;
use Viserio\Contract\Container\Pipe as PipeContract;

class DecoratorServicePipe implements PipeContract
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilderContract $containerBuilder): void
    {
        $definitions = new SplPriorityQueue();
        $order = \PHP_INT_MAX;

        foreach ($containerBuilder->getDefinitions() as $id => $definition) {
            if ($definition instanceof DecoratorAwareDefinitionContract && null !== $decorated = $definition->getDecorator()) {
                $definitions->insert([$id, $definition], [$decorated[2], --$order]);
            }
        }

        $decoratingDefinitions = [];

        foreach ($definitions as [$id, $definition]) {
            [$inner, $renamedId] = $definition->getDecorator();

            $definition->removeDecorator();

            if (! $renamedId) {
                $renamedId = $id . '.inner';
            }

            $definition->innerServiceId = $renamedId;

            // we create a new alias/service for the service we are replacing
            // to be able to reference it in the new one
            if ($containerBuilder->hasAlias($inner)) {
                $alias = $containerBuilder->getAlias($inner);
                $public = $alias->isPublic();

                $containerBuilder->setAlias($alias->getName(), $renamedId);
            } else {
                $decoratedDefinition = $containerBuilder->getDefinition($inner);
                $public = $decoratedDefinition->isPublic();
                $decoratedDefinition->setPublic(false);

                $containerBuilder->setDefinition($renamedId, $decoratedDefinition);

                $decoratingDefinitions[$inner] = $decoratedDefinition;
            }

            if (isset($decoratingDefinitions[$inner])) {
                $decoratingDefinition = $decoratingDefinitions[$inner];

                $definition->setTags(\array_merge($decoratingDefinition->getTags(), $definition->getTags()));
                $decoratingDefinition->setTags([]);

                $decoratingDefinitions[$inner] = $definition;
            }

            $containerBuilder->setAlias($id, $inner)->setPublic($public);
        }
    }
}
