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

use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;

/**
 * @internal
 */
final class RemoveUnusedDefinitionsPipe extends AbstractRecursivePipe
{
    /** @var array */
    private $connectedIds = [];

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilderContract $containerBuilder): void
    {
        try {
            $this->containerBuilder = $containerBuilder;
            $connectedIds = [];
            $aliases = $containerBuilder->getAliases();

            foreach ($aliases as $id => $alias) {
                if ($alias->isPublic()) {
                    $this->connectedIds[] = $alias->getName();
                }
            }

            foreach ($containerBuilder->getDefinitions() as $id => $definition) {
                if ($definition->isPublic()) {
                    $connectedIds[$id] = true;

                    $this->processValue($definition);
                }
            }

            while ($this->connectedIds) {
                $ids = $this->connectedIds;
                $this->connectedIds = [];

                foreach ($ids as $id) {
                    if (! isset($connectedIds[$id]) && $containerBuilder->hasDefinition($id)) {
                        $connectedIds[$id] = true;

                        $this->processValue($containerBuilder->getDefinition($id));
                    }
                }
            }

            foreach ($containerBuilder->getDefinitions() as $id => $definition) {
                if (! isset($connectedIds[$id])) {
                    $containerBuilder->removeDefinition($id);
                    $containerBuilder->log($this, \sprintf('Removed service [%s]; reason: unused.', $id));
                }
            }
        } finally {
            $this->containerBuilder = null;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function processValue($value, $isRoot = false)
    {
        if (! $value instanceof ReferenceDefinition) {
            return parent::processValue($value, $isRoot);
        }

        if (2/* ReferenceDefinition::IGNORE_ON_UNINITIALIZED_REFERENCE */ !== $value->getBehavior()) {
            $this->connectedIds[] = $value->getName();
        }

        return $value;
    }
}
