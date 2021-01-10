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

interface MethodCallsAwareDefinition
{
    /**
     * Gets the methods to call after service initialization.
     */
    public function getMethodCalls(): array;

    /**
     * Sets the methods to call after service initialization.
     *
     * @return static
     */
    public function setMethodCalls(array $calls = []);

    /**
     * Adds a method to call after service initialization.
     *
     * @param string $method       The method name to call
     * @param array  $parameters   An array of parameters to pass to the method call
     * @param bool   $returnsClone Whether the call returns the service instance or not
     *
     * @throws \Viserio\Contract\Container\Exception\InvalidArgumentException on empty $method param
     *
     * @return static
     */
    public function addMethodCall(string $method, array $parameters = [], bool $returnsClone = false);

    /**
     * Removes a method to call after service initialization.
     *
     * @param string $method The method name to remove
     *
     * @return static
     */
    public function removeMethodCall(string $method);

    /**
     * Check if the current definition has a given method to call after service initialization.
     *
     * @param string $method The method name to search for
     */
    public function hasMethodCall($method): bool;
}
