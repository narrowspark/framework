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

interface ArgumentAwareDefinition
{
    /**
     * Set a list of arguments.
     *
     * @param array $arguments
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
     * @param mixed      $value
     *
     * @return static
     */
    public function setArgument($key, $value);

    /**
     * Gets an argument from key.
     *
     * @param int|string $index
     *
     * @return mixed
     */
    public function getArgument($index);

    /**
     * Returns the list of arguments to pass when calling the method.
     *
     * @return array
     */
    public function getArguments(): array;

    /**
     * Replaces a specific argument.
     *
     * @param int|string $index
     * @param mixed      $argument
     *
     * @throws OutOfBoundsException When the replaced argument does not exist
     *
     * @return static
     */
    public function replaceArgument($index, $argument);
}
