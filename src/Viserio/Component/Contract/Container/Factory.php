<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Container;

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
     * @param callable|object|string $abstract   closure, function, method, class, name or a class name
     * @param array                  $parameters Optional parameters to use to build the entry. Use this to force specific
     *                                           parameters to specific values. Parameters not defined in this array will
     *                                           be automatically resolved.
     *
     * @throws \Viserio\Component\Contract\Container\Exception\BindingResolutionException
     * @throws \Viserio\Component\Contract\Container\Exception\CyclicDependencyException
     *
     * @return mixed
     */
    public function make($abstract, array $parameters = []);
}
