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
     * @param string                                       $id
     * @param \Viserio\Contract\Container\ContainerBuilder $container
     *
     * @throws \Viserio\Contract\Container\Exception\NotFoundException
     *
     * @return string
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
