<?php
declare(strict_types=1);
namespace Viserio\Contracts\Routing;

interface RouteParser
{
    public const STATIC_PART = 0;

    public const PARAMETER_PART = 1;

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
     * @param string   $route
     * @param string[] $conditions
     *
     * @throws \Viserio\Contracts\Routing\Exception\InvalidRoutePatternException
     *
     * @return \Viserio\Contracts\Routing\RouteSegment[]
     */
    public function parse(string $route, array $conditions): array;
}
