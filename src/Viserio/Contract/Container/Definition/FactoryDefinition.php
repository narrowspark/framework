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

namespace Viserio\Contract\Container\Definition;

use OutOfBoundsException;

interface FactoryDefinition extends ArgumentAwareDefinition, AutowiredAwareDefinition, DecoratorAwareDefinition, Definition, MethodCallsAwareDefinition, PropertiesAwareDefinition, TagAwareDefinition
{
    /**
     * Get the class of this definition.
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
     *
     * @return static
     */
    public function setClassArgument($key, $value);

    /**
     * Gets an class parameter from key.
     *
     * @param int|string $index
     */
    public function getClassArgument($index);

    /**
     * Get the factory method.
     */
    public function getMethod(): string;

    /**
     * Replaces a specific class parameter.
     *
     * @param int|string $index
     *
     * @throws OutOfBoundsException When the replaced argument does not exist
     *
     * @return static
     */
    public function replaceClassArgument($index, $parameter);

    /**
     * Returns a list of class parameters.
     */
    public function getClassArguments(): array;

    /**
     * Set a list of class parameters.
     *
     * @return static
     */
    public function setClassArguments(array $arguments);

    /**
     * Check if the method is static.
     */
    public function isStatic(): bool;

    /**
     * Set true if the method is static or false if not.
     *
     * @return static
     */
    public function setStatic(bool $static);

    /**
     * Get the method return type.
     */
    public function getReturnType(): ?string;

    /**
     * Set the method return type.
     *
     * @return static
     */
    public function setReturnType(string $type);
}
