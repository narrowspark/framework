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

namespace Viserio\Component\Container\Pipeline;

use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\Definition\ReferenceDefinition as ReferenceDefinitionContract;

/**
 * @internal
 */
final class ResolveReferenceAliasesToDependencyReferencesPipe extends AbstractRecursivePipe
{
    /**
     * {@inheritdoc}
     */
    protected function processValue($value, $isRoot = false)
    {
        if ($value instanceof ReferenceDefinitionContract) {
            $refName = $value->getName();
            $changedRefType = false;

            if (null === $refType = $value->getType()) {
                $changedRefType = true;
                $refType = $refName;
            }

            if ($refType !== $defId = $this->getDefinitionId($refType, $this->containerBuilder)) {
                $ref = new ReferenceDefinition($defId, $value->getBehavior());

                if (! $changedRefType) {
                    $ref->setType($refType);
                }

                if (($variableName = $value->getVariableName()) !== null) {
                    $ref->setVariableName($variableName);
                }

                $ref->setMethodCalls($value->getMethodCalls());

                return $ref;
            }
        }

        return parent::processValue($value);
    }

    /**
     * Get definition id.
     *
     * @throws \Viserio\Contract\Container\Exception\NotFoundException
     */
    private function getDefinitionId(string $id, ContainerBuilderContract $container): string
    {
        if ($container->hasAlias($id)) {
            $alias = $container->getAlias($id);

            if ($alias->isDeprecated()) {
                @trigger_error($alias->getDeprecationMessage(), \E_USER_DEPRECATED);
            }

            $id = $alias->getName();
        }

        return $id;
    }
}
