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
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Definition\ParameterDefinition;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Contract\Container\Argument\Argument as ArgumentContract;
use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\Definition\ClosureDefinition as ClosureDefinitionContract;
use Viserio\Contract\Container\Definition\Definition as DefinitionContract;
use Viserio\Contract\Container\Definition\FactoryDefinition as FactoryDefinitionContract;
use Viserio\Contract\Container\Definition\ObjectDefinition as ObjectDefinitionContract;
use Viserio\Contract\Container\Definition\ReferenceDefinition as ReferenceDefinitionContract;
use Viserio\Contract\Container\Exception\CircularDependencyException;

/**
 * @internal
 */
final class InlineServiceDefinitionsPipe extends AbstractRecursivePipe
{
    /** @var array */
    private $cloningIds = [];

    /** @var array */
    private $connectedIds = [];

    /** @var array */
    private $notInlinedIds = [];

    /** @var array<string, bool> */
    private $inlinedIds = [];

    /**
     * A dependency graph instance.
     *
     * @var \Viserio\Contract\Container\ServiceReferenceGraph
     */
    private $dependencyGraph;

    /** @var null|\Viserio\Component\Container\Pipeline\AnalyzeServiceDependenciesPipe */
    private $analyzingPipe;

    /**
     * Create a new InlineServiceDefinitionsPipe instance.
     *
     * @param null|\Viserio\Component\Container\Pipeline\AnalyzeServiceDependenciesPipe $analyzingPipe
     */
    public function __construct(AnalyzeServiceDependenciesPipe $analyzingPipe = null)
    {
        $this->analyzingPipe = $analyzingPipe;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilderContract $containerBuilder): void
    {
        $this->containerBuilder = $containerBuilder;

        if ($this->analyzingPipe !== null) {
            $analyzedContainer = new ContainerBuilder();
            $analyzedContainer->getServiceReferenceGraph()->reset();
            $analyzedContainer->setAliases($containerBuilder->getAliases());

            foreach ($containerBuilder->getDefinitions() as $id => $definition) {
                $analyzedContainer->setDefinition($id, $definition);
            }
        } else {
            $analyzedContainer = $containerBuilder;
        }

        try {
            /** @var array<string, bool> $remainingInlinedIds */
            $remainingInlinedIds = [];
            $this->connectedIds = $this->notInlinedIds = $containerBuilder->getDefinitions();

            do {
                if ($this->analyzingPipe !== null) {
                    foreach (\array_intersect_key($analyzedContainer->getDefinitions(), $this->connectedIds) as $id => $definition) {
                        $analyzedContainer->setDefinition($id, $definition);
                    }

                    $this->analyzingPipe->process($analyzedContainer);
                }

                $this->dependencyGraph = $analyzedContainer->getServiceReferenceGraph();
                $notInlinedIds = $this->notInlinedIds;
                $this->connectedIds = $this->notInlinedIds = $this->inlinedIds = [];

                foreach ($analyzedContainer->getDefinitions() as $id => $definition) {
                    if (! $this->dependencyGraph->hasNode($id)) {
                        continue;
                    }

                    foreach ($this->dependencyGraph->getNode($id)->getOutEdges() as $edge) {
                        if (\array_key_exists($edge->getSourceNode()->getId(), $notInlinedIds)) {
                            $this->currentId = $id;

                            $this->processValue($definition, true);

                            break;
                        }
                    }
                }

                foreach ($this->inlinedIds as $id => $isPublicOrNotShared) {
                    if ($isPublicOrNotShared === true) {
                        $remainingInlinedIds[$id] = $id;
                    } else {
                        $containerBuilder->removeDefinition($id);
                        $analyzedContainer->removeDefinition($id);
                    }
                }
            } while (\count($this->inlinedIds) !== 0 && $this->analyzingPipe !== null);

            foreach ($remainingInlinedIds as $id) {
                $definition = $containerBuilder->getDefinition($id);

                if (! $definition->isShared() && ! $definition->isPublic()) {
                    $containerBuilder->removeDefinition($id);
                }
            }
        } finally {
            $this->containerBuilder = $this->dependencyGraph = null;
            $this->connectedIds = $this->notInlinedIds = $this->inlinedIds = [];
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function processValue($value, bool $isRoot = false)
    {
        if ($value instanceof ReferenceDefinitionContract && $value->getName() === ContainerInterface::class) {
            $this->containerBuilder->log($this, \sprintf('Inlined service [%s] to [%s].', $value->getName(), $this->currentId));

            return $value;
        }

        if ($value instanceof ParameterDefinition || $value instanceof ArgumentContract) {
            return $value;
        }

        if ($value instanceof DefinitionContract && \count($this->cloningIds) !== 0) {
            if ($value->isShared()) {
                return $value;
            }

            $value = clone $value;
        }

        if (! $value instanceof ReferenceDefinitionContract) {
            return parent::processValue($value, $isRoot);
        }

        if (! $this->containerBuilder->hasDefinition($id = $value->getName())) {
            return $value;
        }

        if (! $this->isInlineableDefinition($id, $definition = $this->containerBuilder->getDefinition($id))) {
            return $value;
        }

        $this->containerBuilder->log($this, \sprintf('Inlined service [%s] to [%s].', $id, $this->currentId));

        $this->inlinedIds[$id] = $definition->isPublic() || ! $definition->isShared();
        $this->notInlinedIds[$this->currentId] = true;

        if ($definition->isShared()) {
            return $definition;
        }

        if (\array_key_exists($id, $this->cloningIds)) {
            $ids = \array_keys($this->cloningIds);
            $ids[] = $id;

            throw new CircularDependencyException($id, \array_slice($ids, \array_search($id, $ids, true)));
        }

        $this->cloningIds[$id] = true;

        try {
            return $this->processValue($definition);
        } finally {
            unset($this->cloningIds[$id]);
        }
    }

    /**
     * Checks if the definition is inlineable.
     *
     * @param string                                            $id
     * @param \Viserio\Contract\Container\Definition\Definition $definition
     *
     * @throws \Viserio\Contract\Container\Exception\NotFoundException
     *
     * @return bool If the definition is inlineable
     */
    private function isInlineableDefinition(string $id, DefinitionContract $definition): bool
    {
        if ($definition->isDeprecated() || $definition->isLazy() || $definition->isSynthetic()) {
            return false;
        }

        $srcId = null;

        if (! $definition->isShared()) {
            if (! $this->dependencyGraph->hasNode($id)) {
                return true;
            }

            foreach ($this->dependencyGraph->getNode($id)->getInEdges() as $edge) {
                $srcId = $edge->getSourceNode()->getId();
                $this->connectedIds[$srcId] = true;

                if ($edge->isWeak() || $edge->isLazy()) {
                    return false;
                }
            }

            return true;
        }

        if ($definition->isPublic()) {
            return false;
        }

        if (! $this->dependencyGraph->hasNode($id)) {
            return true;
        }

        if ($this->currentId === $id) {
            return false;
        }

        $this->connectedIds[$id] = true;
        $srcIds = [];
        $srcCount = 0;
        $isReferencedByConstructor = false;

        foreach ($this->dependencyGraph->getNode($id)->getInEdges() as $edge) {
            $isReferencedByConstructor = $isReferencedByConstructor || $edge->isReferencedByConstructor();

            $srcId = $edge->getSourceNode()->getId();

            $this->connectedIds[$srcId] = true;

            if ($edge->isWeak() || $edge->isLazy()) {
                return false;
            }

            $srcIds[$srcId] = true;
            $srcCount++;
        }

        if (\count($srcIds) !== 1) {
            $this->notInlinedIds[$id] = true;

            return false;
        }

        if ($definition instanceof ClosureDefinitionContract || ($srcCount > 1 && $definition instanceof FactoryDefinitionContract && $definition->getClass() === ReferenceDefinition::class)) {
            return false;
        }

        $containerDefinition = null;

        if ($srcId !== null && $this->containerBuilder->hasDefinition($srcId)) {
            $containerDefinition = $this->containerBuilder->getDefinition($srcId);
        }

        if ($isReferencedByConstructor && $containerDefinition !== null && $definition instanceof ObjectDefinitionContract && $containerDefinition->isLazy() && \count($definition->getProperties()) !== 0 && \count($definition->getMethodCalls()) !== 0) {
            return false;
        }

        return $containerDefinition !== null && $containerDefinition->isShared();
    }
}
