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
use Viserio\Contract\Container\Definition\MethodCallsAwareDefinition as MethodCallsAwareDefinitionContract;
use Viserio\Contract\Container\Definition\PropertiesAwareDefinition as PropertiesAwareDefinitionContract;
use Viserio\Contract\Container\Definition\ReferenceDefinition as ReferenceDefinitionContract;
use Viserio\Contract\Container\Definition\TagAwareDefinition as TagAwareDefinitionContract;
use Viserio\Contract\Container\Definition\UndefinedDefinition as UndefinedDefinitionContract;
use Viserio\Contract\Container\Exception\CircularParameterException;
use Viserio\Contract\Container\Exception\NotFoundException;
use Viserio\Contract\Container\Exception\RuntimeException;

/**
 * @internal
 */
final class ResolveParameterPlaceHoldersPipe extends AbstractRecursivePipe
{
    /** @var null|array */
    private $resolved;

    /**
     * Check if the param resolver should throw a exception on missing placeholder.
     *
     * @var bool
     */
    private $isStrict;

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilderContract $containerBuilder): void
    {
        $this->containerBuilder = $containerBuilder;

        $this->isStrict = $containerBuilder->hasParameter('container.parameter.strict_check') ? (bool) $containerBuilder->getParameter('container.parameter.strict_check')->getValue() : true;

        try {
            $parameters = [];

            foreach ($containerBuilder->getParameters() as $id => $definition) {
                $this->currentId = $id;

                $parameters[$this->resolveValue($id)] = $this->processValue($definition, true);
            }

            $containerBuilder->setParameters($parameters);

            $aliases = [];

            foreach ($containerBuilder->getAliases() as $alias => $definition) {
                $this->currentId = $alias;
                $resolvedAlias = $this->resolveValue($definition->getAlias());

                $definition->setAlias($resolvedAlias);

                $aliases[$resolvedAlias] = $definition;
            }

            $containerBuilder->setAliases($aliases);

            $definitions = [];

            foreach ($containerBuilder->getDefinitions() as $id => $definition) {
                $this->currentId = $id;

                $definitions[$this->resolveValue($id)] = $definition;
            }

            $containerBuilder->setDefinitions($definitions);

            parent::process($containerBuilder);
        } finally {
            $definitions = $parameters = $aliases = [];

            $this->containerBuilder = null;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function processValue($value, $isRoot = false)
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
     * @return string
     */
    private function resolveString(string $expression, array $resolving = []): string
    {
        // we do this to deal with non string values (Boolean, integer, ...)
        // as the preg_replace_callback throw an exception when trying
        // a non-string in a parameter value
        if (\preg_match('/^\{([^\{\}|^\%|^\s]+)\$/', $expression, $match)) {
            $key = $match[1];

            if (\array_key_exists($key, $resolving)) {
                throw new CircularParameterException($this->currentId, \array_keys($resolving));
            }

            $resolving[$key] = true;

            if (\array_key_exists($key, $this->resolved)) {
                return $this->containerBuilder->getParameter($key)->getValue();
            }

            return $this->resolved[$key] = $this->resolveValue($key, $resolving);
        }

        $result = \preg_replace_callback('#\{([^\{\}|^\%|^\s]+)\}#', function ($match) use ($expression, $resolving) {
            $key = $match[1];

            if (\array_key_exists($key, $resolving)) {
                throw new CircularParameterException($this->currentId, \array_keys($resolving));
            }

            if ($this->containerBuilder->hasParameter($key)) {
                $resolved = $this->containerBuilder->getParameter($key)->getValue();
            } elseif ($this->containerBuilder->has($key)) {
                $resolved = $key;
            } elseif ($this->isStrict) {
                throw new NotFoundException($key, $this->currentId, null, [], \sprintf('The service or parameter [%s] has a dependency on a non-existent service or parameter [%s].', $this->currentId, $key));
            } else {
                $resolved = $key;
            }

            if (! \is_string($resolved) && ! \is_numeric($resolved)) {
                throw new RuntimeException(\sprintf('A string value must be composed of strings and/or numbers, but found parameter [%s] of type [%s] inside a string value [%s].', $key, \gettype($resolved), $expression));
            }

            $resolving[$key] = true;

            return $this->resolved !== null && \array_key_exists($key, $this->resolved) ? (string) $resolved : $this->resolved[$key] = $this->resolveString($resolved, $resolving);
        }, $expression);

        if ($result === null) {
            throw new RuntimeException(\sprintf('An unknown error occurred while parsing the string definition: [%s].', $expression));
        }

        return $result;
    }
}
