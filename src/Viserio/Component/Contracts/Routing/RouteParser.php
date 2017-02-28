<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Routing;

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
     * @throws \Viserio\Component\Contracts\Routing\Exception\InvalidRoutePatternException
     *
     * @return \Viserio\Component\Contracts\Routing\RouteSegment[]
     */
    public static function parse(string $route, array $conditions): array;
}
