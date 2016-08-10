<?php
declare(strict_types=1);
namespace Viserio\Contracts\Routing;

interface RouteParser
{
    /**
     * Parses the supplied route pattern into an array of route segments.
     *
     * Example: 'user/{id}/create'
     * Should return: [
     *     StaticSegment{ $value => 'user' },
     *     ParameterSegment{ $name => 'id', $match => '[0-9]+' },
     *     StaticSegment{ $value => 'create' },
     * ]
     *
     * @param string $route
     *
     * @return \Viserio\Contracts\Routing\RouteSegment[]
     *
     * @throws \Viserio\Contracts\Routing\Exception\InvalidRoutePatternException
     */
    public function parse(string $route): array;
}
