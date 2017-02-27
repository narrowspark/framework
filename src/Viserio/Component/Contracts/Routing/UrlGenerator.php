<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Routing;

interface UrlGenerator
{
    /**
     * Get the URL to a named route.
     *
     * @param string $name
     * @param array  $parameters
     * @param bool   $absolute
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public function route(string $name, array $parameters = [], bool $absolute = true): string;
}
