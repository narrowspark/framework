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
     * @param bool                   $shared
     *
     * @throws \Viserio\Contract\Container\Exception\BindingResolutionException
     * @throws \Viserio\Contract\Container\Exception\CircularDependencyException
     *
     * @return mixed
     */
    public function make($abstract, array $arguments = [], bool $shared = true);
}
