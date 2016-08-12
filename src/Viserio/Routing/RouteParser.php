<?php
declare(strict_types=1);
namespace Viserio\Routing;

use Viserio\Contracts\Routing\{
    Exception\InvalidRoutePatternException,
    RouteParser as RouteParserContract,
    RouteSegment as RouteSegmentContract
};

class RouteParser implements RouteParserContract
{
    /**
     * {@inheritdoc}
     */
    public function parse(string $route): array
    {
        if (strlen($route) > 1 && $route[0] !== '/') {
            throw new InvalidRoutePatternException(sprintf(
                'Invalid route pattern: non-root route must be prefixed with \'/\', \'%s\' given',
                $route
            ));
        }

        list($patternString, $conditions) = $this->parseRoutingPattern($route);

        $segments = [];

        return $segments;
    }

    protected function parseRoutingPattern($pattern)
    {
        if (is_string($pattern)) {
            return [$pattern, []];
        }

        if (is_array($pattern)) {
            if (!isset($pattern[0]) || !is_string($pattern[0])) {
                throw new InvalidRoutePatternException(sprintf(
                    'Cannot add route: route pattern array must have the first element containing the pattern string, %s given',
                    isset($pattern[0]) ? gettype($pattern[0]) : 'none'
                ));
            }

            $patternString = $pattern[0];
            $parameterConditions = $pattern;

            unset($parameterConditions[0]);

            return [$patternString, $parameterConditions];
        }

        throw new InvalidRoutePatternException(sprintf(
            'Cannot add route: route pattern must be a pattern string or array, %s given',
            gettype($pattern)
        ));
    }
}
