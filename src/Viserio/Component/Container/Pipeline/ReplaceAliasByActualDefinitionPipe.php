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

use Psr\Container\ContainerInterface;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\Definition\ReferenceDefinition as ReferenceDefinitionContract;

/**
 * @internal
 *
 * Replaces aliases with actual service definitions, effectively removing these
 * aliases
 */
final class ReplaceAliasByActualDefinitionPipe extends AbstractRecursivePipe
{
    /**
     * A list of alias replacements.
     *
     * @var array
     */
    private $replacements = [];

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilderContract $containerBuilder): void
    {
        // First collect all alias targets that need to be replaced
        $seenAliasTargets = [];

        foreach ($containerBuilder->getAliases() as $alias => $aliasDefinition) {
            $targetId = $aliasDefinition->getName();

            // Special case: leave this target alone
            if ($targetId === ContainerInterface::class) {
                continue;
            }

            // Check if target needs to be replaces
            if (isset($this->replacements[$targetId])) {
                $containerBuilder->setAlias($alias, $this->replacements[$targetId])
                    ->setPublic($aliasDefinition->isPublic());
            }

            // No need to process the same target twice
            if (isset($seenAliasTargets[$targetId])) {
                continue;
            }

            $seenAliasTargets[$targetId] = true;

            $definition = $containerBuilder->getDefinition($targetId);

            if ($definition->isPublic()) {
                continue;
            }

            // Remove private definition and schedule for replacement
            $definition->setPublic($aliasDefinition->isPublic());

            $containerBuilder->setDefinition($alias, $definition);
            $containerBuilder->removeDefinition($targetId);

            $this->replacements[$targetId] = $alias;
        }

        parent::process($containerBuilder);

        $this->replacements = [];
    }

    /**
     * {@inheritdoc}
     */
    protected function processValue($value, $isRoot = false)
    {
        if ($value instanceof ReferenceDefinitionContract && isset($this->replacements[$referenceId = $value->getName()])) {
            // Perform the replacement
            $newId = $this->replacements[$referenceId];
            $ref = new ReferenceDefinition($newId, $value->getBehavior());
            $ref->setVariableName($value->getVariableName());
            $ref->setMethodCalls($value->getMethodCalls());

            $value = $ref;

            $this->containerBuilder->log($this, \sprintf('Changed reference of service [%s] previously pointing to [%s] to [%s].', $this->currentId, $referenceId, $newId));
        }

        return parent::processValue($value, $isRoot);
    }
}
