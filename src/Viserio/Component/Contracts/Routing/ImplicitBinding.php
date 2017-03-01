<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Routing;

interface ImplicitBinding
{
    /**
     * Resolve the implicit route bindings for the given route.
     *
     * @param \Viserio\Component\Contracts\Routing\Route $route
     *
     * @return \Viserio\Component\Contracts\Routing\Route
     */
    public function resolve(Route $route): Route;
}
