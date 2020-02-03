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

use Viserio\Component\Container\Definition\ParameterDefinition;
use Viserio\Component\Container\Traits\ParameterProcessResolvingTrait;
use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\Definition\ChangeAwareDefinition as ChangeAwareDefinitionContract;
use Viserio\Contract\Container\Definition\Definition;
use Viserio\Contract\Container\Definition\MethodCallsAwareDefinition as MethodCallsAwareDefinitionContract;
use Viserio\Contract\Container\Definition\PropertiesAwareDefinition as PropertiesAwareDefinitionContract;
use Viserio\Contract\Container\Definition\ReferenceDefinition as ReferenceDefinitionContract;
use Viserio\Contract\Container\Definition\TagAwareDefinition as TagAwareDefinitionContract;
use Viserio\Contract\Container\Definition\UndefinedDefinition as UndefinedDefinitionContract;

/**
 * @internal
 */
final class ResolveParameterProcessorPlaceHolderPipe extends AbstractRecursivePipe
{
    use ParameterProcessResolvingTrait;

    private iterable $processors = [];

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilderContract $containerBuilder): void
    {
        $this->containerBuilder = $containerBuilder;

        if ($this->containerBuilder->hasDefinition(RegisterParameterProcessorsPipe::PROCESSORS_KEY)) {
            /** @var \Viserio\Contract\Container\Definition\IteratorDefinition $iteratorDefinition */
            $iteratorDefinition = $this->containerBuilder->getDefinition(RegisterParameterProcessorsPipe::PROCESSORS_KEY);

            foreach ($iteratorDefinition->getArgument() as $definition) {
                $class = $this->containerBuilder->findDefinition($definition->getName())
                    ->getValue();

                $reflection = $this->containerBuilder->getClassReflector($class);

                $this->processors[] = $reflection->newInstanceWithoutConstructor();
            }
        }

        try {
            $this->processValue($containerBuilder->getParameters(), true);
            $this->processValue($containerBuilder->getDefinitions(), true);
        } finally {
            // no needed in the compiled container
            $this->containerBuilder->removeDefinition(RegisterParameterProcessorsPipe::PROCESSORS_KEY);
            $this->containerBuilder->removeParameter(RegisterParameterProcessorsPipe::PROCESSOR_TYPES_PARAMETER_KEY);
            $this->containerBuilder = null;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getProcessors(): iterable
    {
        return $this->processors;
    }

    /**
     * {@inheritdoc}
     */
    protected function processValue($value, bool $isRoot = false)
    {
        if (\is_string($value)) {
            return $this->resolveValue($value);
        }

        if ($value instanceof ParameterDefinition) {
            $value->setValue($this->processValue($value->getValue()));
        }

        if ($value instanceof Definition) {
            $value->setName((string) $this->processValue($value->getName()));
        }

        if ($value instanceof ChangeAwareDefinitionContract) {
            if ($value instanceof PropertiesAwareDefinitionContract && $value->getChange('properties')) {
                $value->setProperties($this->processValue($value->getProperties()));
            }

            if ($value instanceof TagAwareDefinitionContract && $value->getChange('tags')) {
                $value->setTags($this->processValue($value->getTags()));
            }

            if ($value instanceof MethodCallsAwareDefinitionContract && ($value instanceof ReferenceDefinitionContract ?? $value->getChange('method_calls'))) {
                $value->setMethodCalls($this->processValue($value->getMethodCalls()));
            }
        }

        if ($value instanceof UndefinedDefinitionContract) {
            $value->setValue($this->processValue($value->getValue()));

            if ($value->getChange('properties')) {
                $value->setProperties($this->processValue($value->getProperties()));
            }

            if ($value->getChange('arguments')) {
                $value->setArguments($this->processValue($value->getArguments()));
            }

            if ($value->getChange('decorated_service')) {
                $value->decorate((string) $this->processValue($value->getDecorator()));
            }

            if ($value->getChange('method_calls')) {
                $value->setMethodCalls($this->processValue($value->getMethodCalls()));
            }
        }

        $value = parent::processValue($value, $isRoot);

        if (\is_array($value)) {
            $value = \array_combine($this->resolveValue(\array_keys($value)), $value);
        }

        return $value;
    }

    /**
     * Replaces process parameter placeholders ({name|process}) by their values.
     *
     * @param mixed $value
     * @param array $resolving An array of keys that are being resolved (used internally to detect circular references)
     *
     * @throws \Viserio\Contract\Container\Exception\CircularParameterException if a circular reference if detected
     * @throws \Viserio\Contract\Container\Exception\RuntimeException           when a given parameter has a type problem
     *
     * @return mixed The resolved value
     */
    protected function resolveValue($value)
    {
        if (\is_array($value)) {
            $args = [];

            foreach ($value as $k => $v) {
                $args[$k] = $this->resolveValue($v);
            }

            return $args;
        }

        if (! \is_string($value) || 2 > \strlen($value)) {
            return $value;
        }

        return $this->resolveString($value) ?? $value;
    }
}
