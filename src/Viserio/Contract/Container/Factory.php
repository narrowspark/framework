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

interface Factory
{
    /**
     * Resolves an entry by it type.
     *
     * This method behave like get() except resolves the entry again every time.
     * For example if the entry is a class then a new instance will be created each time.
     *
     * This method makes the container behave like a factory.
     *
     * @param callable|object|string $abstract  closure, function, method, object or a class name
     * @param array                  $arguments Optional arguments to use to build the entry. Use this to force specific
     *                                          arguments to specific values. Arguments not defined in this array will
     *                                          be automatically resolved.
     *
     * @throws \Viserio\Contract\Container\Exception\BindingResolutionException
     * @throws \Viserio\Contract\Container\Exception\CircularDependencyException
     */
    public function make($abstract, array $arguments = [], bool $shared = true);
}
