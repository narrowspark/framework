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

namespace Viserio\Contract\Container\ServiceProvider;

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
     * @param string                            $abstract
     * @param null|array|\Closure|object|string $concrete
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
     * @param string                            $abstract
     * @param null|array|\Closure|object|string $concrete
     *
     * @return ClosureDefinitionContract|DefinitionContract|FactoryDefinitionContract|ObjectDefinitionContract|UndefinedDefinitionContract
     */
    public function singleton(string $abstract, $concrete = null);

    /**
     * Register a new Parameter to the container.
     *
     * @param string $id
     * @param mixed  $value
     *
     * @return \Viserio\Contract\Container\Definition\Definition
     */
    public function setParameter(string $id, $value): DefinitionContract;

    /**
     * Removes an definition from the container.
     *
     * @param string $abstract Identifier of the entry to remove
     *
     * @return void
     */
    public function remove(string $abstract): void;

    /**
     * Removes a parameter from the container.
     *
     * @param string $id
     *
     * @return void
     */
    public function removeParameter(string $id): void;

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * @param string $id identifier of the entry to look for
     *
     * @return bool
     */
    public function has(string $id): bool;

    /**
     * Alias a type to a different name.
     *
     * @param string $original
     * @param string $alias
     *
     * @return \Viserio\Contract\Container\Definition\AliasDefinition
     */
    public function setAlias(string $original, string $alias): AliasDefinitionContract;

    /**
     * Removes an alias.
     *
     * @param string $alias The alias to remove
     *
     * @return void
     */
    public function removeAlias(string $alias): void;

    /**
     * Overwrite the aliases.
     *
     * @param array $aliases
     *
     * @return void
     */
    public function setAliases(array $aliases): void;
}
