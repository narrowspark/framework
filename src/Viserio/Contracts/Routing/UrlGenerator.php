<?php
declare(strict_types=1);
namespace Viserio\Contracts\Routing;

interface UrlGenerator
{
    /**
     * Generate a URL for the given route.
     *
     * @param string $name       The name of the route to generate a url for
     * @param array  $parameters Parameters to pass to the route
     * @param bool   $absolute   If true, the generated route should be absolute
     *
     * @return string
     */
    public function generate(string $name, array $parameters = [], bool $absolute = false): string;
}
