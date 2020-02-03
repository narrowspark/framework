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
use Viserio\Contract\Container\Argument\Argument as ArgumentContract;
use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\Definition\AliasDefinition as AliasDefinitionContract;
use Viserio\Contract\Container\Definition\ArgumentAwareDefinition as ArgumentAwareDefinitionContract;
use Viserio\Contract\Container\Definition\Definition as DefinitionContract;
use Viserio\Contract\Container\Definition\FactoryDefinition as FactoryDefinitionContract;
use Viserio\Contract\Container\Definition\MethodCallsAwareDefinition as MethodCallsAwareDefinitionContract;
use Viserio\Contract\Container\Definition\ObjectDefinition as ObjectDefinitionContract;
use Viserio\Contract\Container\Definition\PropertiesAwareDefinition as PropertiesAwareDefinitionContract;
use Viserio\Contract\Container\Definition\ReferenceDefinition as ReferenceDefinitionContract;

/**
 * Run this pass before passes that need to know more about the relation of
 * your services.
 *
 * This class will populate the ServiceReferenceGraph with information. You can
 * retrieve the graph in other passes from the compiler.
 */
final class AnalyzeServiceDependenciesPipe extends AbstractRecursivePipe
{
    /**
     * Check if properties and methods parameter should be analyzed.
     *
     * @var bool
     */
    private bool $onlyConstructorArguments;

    /**
     * Check if proxy dumper is used.
     *
     * @var bool
     */
    private bool $hasProxyDumper;

    /**
     * Check if value is lazy.
     *
     * @var bool
     */
    private bool $lazy;

    /**
     * Check if it was run by constructor parameters.
     *
     * @var bool
     */
    private bool $byConstructor;

    /**
     * The current definition.
     *
     * @var \Viserio\Contract\Container\Definition\Definition
     */
    private $currentDefinition;

    /**
     * A dependency graph instance.
     *
     * @var \Viserio\Contract\Container\ServiceReferenceGraph
     */
    private $dependencyGraph;

    /**
     * The registered type aliases.
     *
     * @var \Viserio\Contract\Container\Definition\AliasDefinition[]
     */
    private array $aliases = [];

    /**
     * The container's definitions.
     *
     * @var \Viserio\Contract\Container\Definition\Definition[]
     */
    private array $definitions = [];

    /**
     * Create a new analyze service dependencies instance.
     *
     * @param bool $onlyConstructorArguments
     * @param bool $hasProxyDumper
     */
    public function __construct(bool $onlyConstructorArguments = false, bool $hasProxyDumper = true)
    {
        $this->onlyConstructorArguments = $onlyConstructorArguments;
        $this->hasProxyDumper = $hasProxyDumper;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilderContract $containerBuilder): void
    {
        $this->containerBuilder = $containerBuilder;
        $this->dependencyGraph = $this->containerBuilder->getServiceReferenceGraph();
        $this->dependencyGraph->reset();

        $this->lazy = false;
        $this->byConstructor = false;
        $this->aliases = $containerBuilder->getAliases();
        $this->definitions = $containerBuilder->getDefinitions();

        foreach ($this->aliases as $id => $alias) {
            $targetId = $this->getDefinitionId($alias);

            $this->dependencyGraph->connect($id, $alias, $targetId, $targetId !== null ? $containerBuilder->getDefinition($targetId) : null);
        }

        try {
            parent::process($containerBuilder);
        } finally {
            $this->aliases = $this->definitions = [];
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function processValue($value, $isRoot = false)
    {
        if ($value instanceof ReferenceDefinitionContract && $value->getName() === ContainerInterface::class) {
            return $value;
        }

        $lazy = $this->lazy;

        if ($value instanceof ArgumentContract) {
            $this->lazy = true;

            parent::processValue($value->getValue());

            $this->lazy = $lazy;

            return $value;
        }

        if ($value instanceof ReferenceDefinitionContract) {
            $targetId = $this->getDefinitionId($value);
            $targetDefinition = $targetId !== null ? $this->containerBuilder->getDefinition($targetId) : null;

            $this->dependencyGraph->connect(
                $this->currentId,
                $this->currentDefinition,
                $targetId,
                $targetDefinition,
                $value,
                $this->lazy || ($targetDefinition !== null && $this->hasProxyDumper && $targetDefinition->isLazy()),
                $value->getBehavior() === 3/* ReferenceDefinitionContract::IGNORE_ON_UNINITIALIZED_REFERENCE */,
                $this->byConstructor
            );

            return $value;
        }

        if (! $value instanceof DefinitionContract) {
            return parent::processValue($value, $isRoot);
        }

        if ($isRoot) {
            if ($value->isSynthetic()) {
                return $value;
            }

            $this->currentDefinition = $value;
        } elseif ($this->currentDefinition === $value) {
            return $value;
        }

        $this->lazy = false;
        $byConstructor = $this->byConstructor;
        $this->byConstructor = $isRoot || $byConstructor;

        if ($value instanceof ArgumentAwareDefinitionContract && ! $value instanceof FactoryDefinitionContract) {
            $this->processValue($value->getArguments());
        }

        if ($value instanceof FactoryDefinitionContract) {
            $this->processValue($value->getValue());
            $this->processValue($value->getClassArguments());
        }

        $setters = [];
        $properties = [];

        if ($value instanceof ObjectDefinitionContract || $value instanceof FactoryDefinitionContract) {
            $setters = $value->getMethodCalls();
            $properties = [];

            foreach ($value->getProperties() as $key => [$v]) {
                $properties[$key] = $v;
            }

            // Any references before a "wither" are part of the constructor-instantiation graph
            $lastWitherIndex = null;

            foreach ($setters as $k => $call) {
                if ($call[2] ?? false) {
                    $lastWitherIndex = $k;
                }
            }

            if ($lastWitherIndex !== null) {
                $this->processValue($properties);
                $setters = $properties = [];

                foreach ($value->getMethodCalls() as $k => $call) {
                    if ($lastWitherIndex === null) {
                        $setters[] = $call;

                        continue;
                    }

                    if ($lastWitherIndex === $k) {
                        $lastWitherIndex = null;
                    }

                    $this->processValue($call);
                }
            }
        }

        $this->byConstructor = $byConstructor;

        if (! $this->onlyConstructorArguments) {
            if ($this->currentDefinition instanceof PropertiesAwareDefinitionContract) {
                $this->processValue($properties);
            }

            if ($value instanceof MethodCallsAwareDefinitionContract) {
                $this->processValue($setters);
            }

            if ($value instanceof FactoryDefinitionContract) {
                $this->processValue($value->getArguments());
            }
        }

        $this->lazy = $lazy;

        return $value;
    }

    /**
     * Find definition id.
     *
     * @param \Viserio\Contract\Container\Definition\AliasDefinition|\Viserio\Contract\Container\Definition\Definition|\Viserio\Contract\Container\Definition\ReferenceDefinition $definition
     *
     * @return null|string
     */
    private function getDefinitionId($definition): ?string
    {
        if ($definition instanceof AliasDefinitionContract) {
            $id = $definition->getAlias();
        } else {
            $id = $definition->getName();
        }

        while (isset($this->aliases[$id])) {
            $id = $this->aliases[$id]->getName();
        }

        return isset($this->definitions[$id]) ? $id : null;
    }
}
