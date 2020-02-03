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
use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\Definition\ChangeAwareDefinition as ChangeAwareDefinitionContract;
use Viserio\Contract\Container\Definition\Definition;
use Viserio\Contract\Container\Definition\ObjectDefinition;
use Viserio\Contract\Container\Definition\PropertiesAwareDefinition as PropertiesAwareDefinitionContract;
use Viserio\Contract\Container\Definition\TagAwareDefinition as TagAwareDefinitionContract;
use Viserio\Contract\Container\Definition\UndefinedDefinition as UndefinedDefinitionContract;
use Viserio\Contract\Container\Exception\CircularParameterException;
use Viserio\Contract\Container\Exception\ParameterNotFoundException;
use Viserio\Contract\Container\Exception\RuntimeException;

/**
 * @internal
 */
final class ResolveParameterPlaceHolderPipe extends AbstractRecursivePipe
{
    /**
     * Check if the param resolver should throw a exception on missing placeholder value.
     *
     * @var bool
     */
    private bool $throwOnResolveException;

    private bool $resolveArrays;

    /** @var null|array<int|string, mixed> */
    private array $resolved = [];

    /** @var array<string, bool> */
    private array $providedTypes;

    private bool $isService = false;

    private bool $isParameter = false;

    private bool $isAlias = false;

    public function __construct($resolveArrays = true, $throwOnResolveException = true)
    {
        $this->resolveArrays = $resolveArrays;
        $this->throwOnResolveException = $throwOnResolveException;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilderContract $containerBuilder): void
    {
        $this->containerBuilder = $containerBuilder;

        $this->providedTypes = \array_merge(
            $containerBuilder->hasParameter(RegisterParameterProcessorsPipe::PROCESSOR_TYPES_PARAMETER_KEY) ? (array) $containerBuilder->getParameter(RegisterParameterProcessorsPipe::PROCESSOR_TYPES_PARAMETER_KEY)->getValue() : [],
            $containerBuilder->hasParameter(RegisterParameterProcessorsPipe::RUNTIME_PROCESSOR_TYPES_PARAMETER_KEY) ? (array) $containerBuilder->getParameter(RegisterParameterProcessorsPipe::RUNTIME_PROCESSOR_TYPES_PARAMETER_KEY)->getValue() : [],
        );

        try {
            $parameters = [];

            foreach ($containerBuilder->getParameters() as $id => $definition) {
                $this->currentId = $id;
                $this->isParameter = true;

                $parameters[$this->resolveValue($id)] = $this->processValue($definition, true);

                $this->isParameter = false;
            }

            $containerBuilder->setParameters($parameters);

            $aliases = [];

            foreach ($containerBuilder->getAliases() as $alias => $definition) {
                $this->isAlias = true;
                $this->currentId = $alias;
                $resolvedAlias = $this->resolveValue($definition->getAlias());

                $definition->setAlias($resolvedAlias);

                $aliases[$resolvedAlias] = $definition;
                $this->isAlias = false;
            }

            $containerBuilder->setAliases($aliases);

            $definitions = [];

            foreach ($containerBuilder->getDefinitions() as $id => $definition) {
                $this->currentId = $id;
                $this->isService = true;

                $definitions[$this->resolveValue($id)] = $definition;

                $this->isService = false;
            }

            $containerBuilder->setDefinitions($definitions);

            $this->isService = true;

            parent::process($containerBuilder);

            $this->isService = false;
        } finally {
            $this->containerBuilder = null;
            $this->resolved = [];
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function processValue($value, $isRoot = false)
    {
        if (\is_string($value)) {
            $v = $this->resolveValue($value);

            return $this->resolveArrays || ! $v || ! \is_array($v) ? $v : $value;
        }

        if ($value instanceof ParameterDefinition) {
            $value->setValue($this->processValue($value->getValue()));

            return $value;
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

            if ($value instanceof ObjectDefinition && $value->getChange('class')) {
                $value->setClass($this->processValue($value->getClass()));
            }
        }

        $value = parent::processValue($value, $isRoot);

        if ($value instanceof UndefinedDefinitionContract) {
            $value->setValue($this->processValue($value->getValue()));

            if ($value->getChange('class')) {
                $value->setClass($this->processValue($value->getClass()));
            }

            if ($value->getChange('class_arguments')) {
                $value->setClass($this->processValue($value->getClass()));
            }

            if ($value->getChange('decorated_service')) {
                $value->decorate((string) $this->processValue($value->getDecorator()));
            }
        }

        if (\is_array($value)) {
            $value = \array_combine($this->resolveValue(\array_keys($value)), $value);
        }

        return $value;
    }

    /**
     * Replaces parameter placeholders ({name}) by their values.
     *
     * @param mixed $value
     * @param array $resolving An array of keys that are being resolved (used internally to detect circular references)
     *
     * @throws \Viserio\Contract\Container\Exception\CircularParameterException if a circular reference if detected
     * @throws \Viserio\Contract\Container\Exception\RuntimeException           when a given parameter has a type problem
     *
     * @return mixed The resolved value
     */
    private function resolveValue($value, array $resolving = [])
    {
        if (\is_array($value)) {
            $args = [];

            foreach ($value as $k => $v) {
                $args[\is_string($k) ? $this->resolveValue($k, $resolving) : $k] = $this->resolveValue($v, $resolving);
            }

            return $args;
        }

        if (! \is_string($value) || 2 > \strlen($value)) {
            return $value;
        }

        return $this->resolveString($value, $resolving);
    }

    /**
     * Resolve a string expression.
     *
     * @param string $expression
     * @param array  $resolving  An array of keys that are being resolved (used internally to detect circular references)
     *
     * @throws \Viserio\Contract\Container\Exception\CircularParameterException if a circular reference if detected
     * @throws \Viserio\Contract\Container\Exception\RuntimeException           when a given parameter has a type problem
     *
     * @return mixed
     */
    private function resolveString(string $expression, array $resolving = [])
    {
        // we do this to deal with non string values (Boolean, integer, ...)
        // as the preg_replace_callback throw an exception when trying
        // a non-string in a parameter value
        if (\preg_match('/^\{([^\{\s]+)\}$/', $expression, $match)) {
            $key = $match[1];

            if (\array_key_exists($key, $resolving)) {
                throw new CircularParameterException($this->currentId, \array_keys($resolving));
            }

            $resolving[$key] = true;

            try {
                return $this->resolved[$key] ??= $this->resolveValue($this->containerBuilder->getParameter($key)->getValue(), $resolving);
            } catch (ParameterNotFoundException $exception) {
                if ($this->throwOnResolveException) {
                    throw new ParameterNotFoundException($key, $this->isService ? $this->currentId : null, $this->isParameter ? $this->currentId : null, null, [], null, $this->isAlias ? \sprintf('The alias [%s] has a dependency on a non-existent parameter [%s].', $this->currentId, $key) : '');
                }

                return $expression;
            }
        }

        $result = \preg_replace_callback('/\{\{|\{([^\{\s]+)\}/', function ($match) use ($expression, $resolving) {
            // skip {{
            if (! isset($match[1])) {
                return '{{';
            }

            $key = $match[1];

            if (\array_key_exists($key, $resolving)) {
                throw new CircularParameterException($this->currentId, \array_keys($resolving));
            }

            try {
                $resolved = $this->containerBuilder->getParameter($key)->getValue();
            } catch (ParameterNotFoundException $exception) {
                $array = \explode('|', $key);

                unset($array[\array_key_first($array)]);

                if ($this->throwOnResolveException && \count(\array_intersect($array, \array_keys($this->providedTypes))) !== 0) {
                    throw new ParameterNotFoundException($key, $this->isService ? $this->currentId : null, $this->isParameter ? $this->currentId : null, null, [], null, $this->isAlias ? \sprintf('The alias [%s] has a dependency on a non-existent parameter [%s].', $this->currentId, $key) : '');
                }

                return $match[0];
            }

            if (! \is_string($resolved) && ! \is_numeric($resolved)) {
                throw new RuntimeException(\sprintf('A string value must be composed of strings and/or numbers, but found parameter [%s] of type [%s] inside a string value [%s].', $key, \gettype($resolved), $expression));
            }

            $resolved = (string) $resolved;
            $resolving[$key] = true;

            return \array_key_exists($key, $this->resolved) ? $resolved : $this->resolved[$key] = $this->resolveString($resolved, $resolving);
        }, $expression);

        if ($result === null) {
            throw new RuntimeException(\sprintf('An unknown error occurred while parsing the string definition: [%s].', $expression));
        }

        return $result;
    }
}
