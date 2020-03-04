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

namespace Viserio\Contract\Container;

use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use Viserio\Contract\Container\Definition\AliasDefinition as AliasDefinitionContract;
use Viserio\Contract\Container\Definition\ClosureDefinition as ClosureDefinitionContract;
use Viserio\Contract\Container\Definition\Definition as DefinitionContract;
use Viserio\Contract\Container\Definition\FactoryDefinition as FactoryDefinitionContract;
use Viserio\Contract\Container\Definition\ObjectDefinition as ObjectDefinitionContract;
use Viserio\Contract\Container\Definition\UndefinedDefinition as UndefinedDefinitionContract;
use Viserio\Contract\Container\Pipe as PipeContract;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ServiceProviderContainerBuilderContract;

interface ContainerBuilder extends ServiceProviderContainerBuilderContract, TaggedContainer
{
    /**
     * Returns the ServiceReferenceGraph instance.
     *
     * @return \Viserio\Contract\Container\ServiceReferenceGraph
     */
    public function getServiceReferenceGraph(): ServiceReferenceGraph;

    /**
     * Returns added definitions.
     *
     * @return ClosureDefinitionContract[]|DefinitionContract[]|FactoryDefinitionContract[]|ObjectDefinitionContract[]|UndefinedDefinitionContract[]
     */
    public function getDefinitions(): array;

    /**
     * Overwrite the parameters.
     */
    public function setParameters(array $parameters): void;

    /**
     * Returns added parameters.
     *
     * @return \Viserio\Contract\Container\Definition\Definition[]
     */
    public function getParameters(): array;

    /**
     * Adds the service definitions.
     *
     * @param \Viserio\Contract\Container\Definition\Definition[] $definitions An array of service definitions
     */
    public function addDefinitions(array $definitions): void;

    /**
     * Sets the service definitions.
     *
     * @param \Viserio\Contract\Container\Definition\Definition[] $definitions An array of service definitions
     */
    public function setDefinitions(array $definitions): void;

    /**
     * Set a definition.
     *
     * @param ClosureDefinitionContract|DefinitionContract|FactoryDefinitionContract|ObjectDefinitionContract|UndefinedDefinitionContract $definition
     */
    public function setDefinition(string $id, DefinitionContract $definition): void;

    /**
     * Gets a service definition by id or alias.
     *
     * The method "unaliases" recursively to return a Definition instance.
     *
     *@throws \Viserio\Contract\Container\Exception\NotFoundException           if the service definition does not exist
     * @throws \Viserio\Contract\Container\Exception\CircularDependencyException
     *
     * @return ClosureDefinitionContract|DefinitionContract|FactoryDefinitionContract|ObjectDefinitionContract|UndefinedDefinitionContract
     */
    public function findDefinition(string $id): DefinitionContract;

    /**
     * Get definition from identifier.
     *
     *@throws \Viserio\Contract\Container\Exception\NotFoundException if the service definition does not exist
     *
     * @return ClosureDefinitionContract|DefinitionContract|FactoryDefinitionContract|ObjectDefinitionContract|UndefinedDefinitionContract
     */
    public function getDefinition(string $id): DefinitionContract;

    /**
     * Removes a service definition from the container.
     *
     * @param string $id The service identifier
     */
    public function removeDefinition(string $id): void;

    /**
     * Get parameter from identifier.
     */
    public function getParameter(string $id): DefinitionContract;

    /**
     * Returns true if the container can return an parameter for the given identifier.
     * Returns false otherwise.
     *
     * @param string $id identifier of the parameter to look for
     */
    public function hasParameter(string $id): bool;

    /**
     * Returns true if the container can return an definition entry for the given identifier.
     * Returns false otherwise.
     *
     * @param string $id identifier of the entry to look for
     */
    public function hasDefinition(string $id): bool;

    /**
     * Check if the container is compiled.
     */
    public function isCompiled(): bool;

    /**
     * Get the reflection object for the object or class name.
     *
     * @throws ReflectionException
     */
    public function getClassReflector(string $class, bool $throw = true): ?ReflectionClass;

    /**
     * Get the reflection object for a method.
     *
     * @throws \Viserio\Contract\Container\Exception\BindingResolutionException
     */
    public function getMethodReflector(ReflectionClass $classReflector, string $method): ReflectionFunctionAbstract;

    /**
     * Get the reflection object for a method.
     *
     * @param Closure|string $function
     *
     * @throws \Viserio\Contract\Container\Exception\BindingResolutionException
     */
    public function getFunctionReflector($function): ReflectionFunction;

    /**
     * Log pipeline processes.
     */
    public function log(PipeContract $pass, string $message): void;

    /**
     * Registers a service provider.
     *
     * @param array $parameters An array of values that customizes the provider
     *
     * @throws \Viserio\Contract\Container\Exception\InvalidArgumentException if object is missing service interfaces
     */
    public function register(object $provider, array $parameters = []): void;

    /**
     * Gets all defined aliases.
     *
     * @return \Viserio\Contract\Container\Definition\AliasDefinition[]
     */
    public function getAliases(): array;

    /**
     * Overwrite the aliases.
     */
    public function setAliases(array $aliases): void;

    /**
     * Alias a type to a different name.
     */
    public function setAlias(string $original, string $alias): AliasDefinitionContract;

    /**
     * Removes an alias.
     *
     * @param string $alias The alias to remove
     */
    public function removeAlias(string $alias): void;

    /**
     * Returns true if an alias exists under the given identifier.
     */
    public function hasAlias(string $id): bool;

    /**
     * Returns a found alias or the given id.
     *
     * @throws \Viserio\Contract\Container\Exception\NotFoundException if the alias does not exist
     */
    public function getAlias(string $id): AliasDefinitionContract;

    /**
     * "Extend" an abstract type in the container.
     */
    public function extend(string $abstract, Closure $closure): void;

    /**
     * Get the extender callbacks for a given type.
     */
    public function getExtenders(string $abstract): array;

    /**
     * Remove all of the extender callbacks for a given type.
     */
    public function removeExtenders(string $abstract): void;

    /**
     * Returns all removed ids.
     *
     * @internal
     */
    public function getRemovedIds(): array;
}
