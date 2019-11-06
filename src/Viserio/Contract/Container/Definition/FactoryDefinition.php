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

namespace Viserio\Contract\Container\Definition;

use OutOfBoundsException;

interface FactoryDefinition extends ArgumentAwareDefinition, AutowiredAwareDefinition, DecoratorAwareDefinition, Definition, MethodCallsAwareDefinition, PropertiesAwareDefinition, TagAwareDefinition
{
    /**
     * Get the class of this definition.
     *
     * @return string
     */
    public function getClass(): string;

    /**
     * Set the class of the definition.
     *
     * @param object|string $class
     */
    public function setClass($class): void;

    /**
     * Sets a specific argument.
     *
     * @param int|string $key
     * @param mixed      $value
     *
     * @return static
     */
    public function setClassArgument($key, $value);

    /**
     * Gets an class parameter from key.
     *
     * @param int|string $index
     *
     * @return mixed
     */
    public function getClassArgument($index);

    /**
     * Get the factory method.
     *
     * @return string
     */
    public function getMethod(): string;

    /**
     * Replaces a specific class parameter.
     *
     * @param int|string $index
     * @param mixed      $parameter
     *
     * @throws OutOfBoundsException When the replaced argument does not exist
     *
     * @return static
     */
    public function replaceClassArgument($index, $parameter);

    /**
     * Returns a list of class parameters.
     *
     * @return array
     */
    public function getClassArguments(): array;

    /**
     * Set a list of class parameters.
     *
     * @param array $arguments
     *
     * @return static
     */
    public function setClassArguments(array $arguments);

    /**
     * Check if the method is static.
     *
     * @return bool
     */
    public function isStatic(): bool;

    /**
     * Set true if the method is static or false if not.
     *
     * @param bool $static
     *
     * @return static
     */
    public function setStatic(bool $static);

    /**
     * Get the method return type.
     *
     * @return null|string
     */
    public function getReturnType(): ?string;

    /**
     * Set the method return type.
     *
     * @param string $type
     *
     * @return static
     */
    public function setReturnType(string $type);
}
