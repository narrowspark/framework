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

interface ArgumentAwareDefinition
{
    /**
     * Set a list of arguments.
     *
     * @return static
     */
    public function setArguments(array $arguments);

    /**
     * Adds an argument to pass to the service constructor/factory method.
     *
     * @param mixed $argument An argument
     *
     * @return static
     */
    public function addArgument($argument);

    /**
     * Sets a specific argument.
     *
     * @param int|string $key
     *
     * @return static
     */
    public function setArgument($key, $value);

    /**
     * Gets an argument from key.
     *
     * @param int|string $index
     */
    public function getArgument($index);

    /**
     * Returns the list of arguments to pass when calling the method.
     */
    public function getArguments(): array;

    /**
     * Replaces a specific argument.
     *
     * @param int|string $index
     *
     * @throws OutOfBoundsException When the replaced argument does not exist
     *
     * @return static
     */
    public function replaceArgument($index, $argument);
}
