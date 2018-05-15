<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Container;

interface Factory
{
    /**
     * Resolves an entry by it type.
     *
     * @param callable|object|string $subject    closure, function, method, class, name or a class name
     * @param array                  $parameters Optional parameters to use to build the entry. Use this to force specific
     *                                           parameters to specific values. Parameters not defined in this array will
     *                                           be automatically resolved.
     *
     * @throws \Viserio\Component\Contract\Container\Exception\BindingResolutionException
     * @throws \Viserio\Component\Contract\Container\Exception\CyclicDependencyException
     *
     * @return mixed
     */
    public function resolve($subject, array $parameters = []);
}
