<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Container;

interface Factory
{
    /**
     * Resolves an entry by its name. If given a class name, it will return a new instance of that class.
     *
     * @param string|object|callable $subject    closure, function, method, class, name or a class name
     * @param array                  $parameters Optional parameters to use to build the entry. Use this to force specific
     *                                           parameters to specific values. Parameters not defined in this array will
     *                                           be automatically resolved.
     *
     * @throws \Viserio\Component\Contracts\Container\Exceptions\BindingResolutionException
     * @throws \Viserio\Component\Contracts\Container\Exceptions\CyclicDependencyException
     *
     * @return mixed
     */
    public function resolve($subject, array $parameters = []);
}
