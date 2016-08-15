<?php
declare(strict_types=1);
namespace Viserio\Routing;

use Viserio\Routing\Matchers\{
    StaticMatcher,
    ParameterMatcher
};
use Viserio\Contracts\Routing\{
    Exceptions\InvalidRoutePatternException,
    RouteParser as RouteParserContract,
    RouteSegment as RouteSegmentContract,
    Pattern
};

class RouteParser implements RouteParserContract
{
    /**
     * {@inheritdoc}
     */
    public function parse(string $route, array $conditions): array
    {
        if (strlen($route) > 1 && $route[0] !== '/') {
            throw new InvalidRoutePatternException(sprintf(
                'Invalid route pattern: non-root route must be prefixed with \'/\', \'%s\' given',
                $route
            ));
        }

        $segments = [];
        $matches = [];
        $names = [];
        $patternSegments = explode('/', $route);

        array_shift($patternSegments);

        foreach ($patternSegments as $key => $patternSegment) {
            if ($this->matchRouteParameters($route, $patternSegment, $conditions, $matches, $names)) {
                $segments[] = new ParameterMatcher(
                    $names,
                    $this->generateRegex($matches, $conditions)
                );
            } else {
                $segments[] = new StaticMatcher($patternSegment);
            }
        }

        return $segments;
    }

    /**
     * Validate and match uri paramters.
     *
     * @param string $pattern
     * @param string $patternSegment
     * @param array  &$conditions
     * @param array  &$matches
     * @param array  &$names
     *
     * @return bool
     */
    protected function matchRouteParameters(
        string $uri,
        string $patternSegment,
        array &$conditions,
        array &$matches,
        array &$names
    ): bool {
        $matchedParameter = false;
        $names = [];
        $matches = [];
        $current = '';
        $inParameter = false;

        foreach (str_split($patternSegment) as $character) {
            if ($inParameter) {
                if ($character === '}') {
                    if (strpos($current, ':') !== false) {
                        $regex = substr($current, strpos($current, ':') + 1);
                        $current = substr($current, 0, strpos($current, ':'));
                        $conditions[$current] = $regex;
                    }

                    $matches[] = [self::PARAMETER_PART, $current];
                    $names[] = $current;
                    $current = '';
                    $inParameter = false;
                    $matchedParameter = true;

                    continue;
                } elseif ($character === '{') {
                    throw new InvalidRoutePatternException(sprintf(
                        'Invalid route uri: cannot contain nested \'{\', \'%s\' given',
                        $uri
                    ));
                }
            } else {
                if ($character === '{') {
                    $matches[] = [self::STATIC_PART, $current];
                    $current = '';
                    $inParameter = true;

                    continue;
                } elseif ($character === '}') {
                    throw new InvalidRoutePatternException(sprintf(
                        'Invalid route uri: cannot contain \'}\' before opening \'{\', \'%s\' given',
                        $uri
                    ));
                }
            }

            $current .= $character;
        }

        if ($inParameter) {
            throw new InvalidRoutePatternException(sprintf(
                'Invalid route uri: cannot contain \'{\' without closing \'}\', \'%s\' given',
                $uri
            ));
        } elseif ($current !== '') {
            $matches[] = [self::STATIC_PART, $current];
        }

        return $matchedParameter;
    }

    /**
     * Generate a segment regex.
     *
     * @param array $matches
     * @param array $parameterPatterns
     *
     * @return string
     */
    protected function generateRegex(array $matches, array $parameterPatterns): string
    {
        $regex = '/^';

        foreach ($matches as $match) {
            list($type, $part) = $match;

            if ($type === self::STATIC_PART) {
                $regex .= preg_quote($part, '/');
            } else {
                // Parameter, $part is the parameter name
                $regex .= '(' . $parameterPatterns[$part] ?? Pattern::ANY . ')';
            }
        }

        $regex .= '$/';

        return $regex;
    }
}
