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

namespace Viserio\Contract\Container\ServiceProvider;

use Closure;
use Viserio\Contract\Container\Definition\AliasDefinition as AliasDefinitionContract;
use Viserio\Contract\Container\Definition\ClosureDefinition as ClosureDefinitionContract;
use Viserio\Contract\Container\Definition\Definition as DefinitionContract;
use Viserio\Contract\Container\Definition\FactoryDefinition as FactoryDefinitionContract;
use Viserio\Contract\Container\Definition\ObjectDefinition as ObjectDefinitionContract;
use Viserio\Contract\Container\Definition\UndefinedDefinition as UndefinedDefinitionContract;

interface ContainerBuilder
{
    /**
     * Register a binding with the container.
     *
     * @param null|array|Closure|object|string $concrete
     *
     * @return ClosureDefinitionContract|DefinitionContract|FactoryDefinitionContract|ObjectDefinitionContract|UndefinedDefinitionContract
     */
    public function bind(string $abstract, $concrete = null);

    /**
     * Register a shared binding in the container.
     *
     * Sometimes, you may wish to bind something into the container that should only be resolved once
     * and the same instance should be returned on subsequent calls into the container.
     *
     * @param null|array|Closure|object|string $concrete
     *
     * @return ClosureDefinitionContract|DefinitionContract|FactoryDefinitionContract|ObjectDefinitionContract|UndefinedDefinitionContract
     */
    public function singleton(string $abstract, $concrete = null);

    /**
     * Register a new Parameter to the container.
     */
    public function setParameter(string $id, $value): DefinitionContract;

    /**
     * Removes an definition from the container.
     *
     * @param string $abstract Identifier of the entry to remove
     */
    public function remove(string $abstract): void;

    /**
     * Removes a parameter from the container.
     */
    public function removeParameter(string $id): void;

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * @param string $id identifier of the entry to look for
     */
    public function has(string $id): bool;

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
     * Overwrite the aliases.
     */
    public function setAliases(array $aliases): void;
}
