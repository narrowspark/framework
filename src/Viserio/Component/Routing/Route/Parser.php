<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Route;

use Viserio\Component\Contract\Routing\Exception\InvalidRoutePatternException;
use Viserio\Component\Contract\Routing\Pattern;
use Viserio\Component\Routing\Matcher\ParameterMatcher;
use Viserio\Component\Routing\Matcher\StaticMatcher;

final class Parser
{
    private const STATIC_PART    = 0;
    private const PARAMETER_PART = 1;

    /**
     * Parses the supplied route pattern into an array of route segments.
     *
     * Example: '/user/{id}/create'
     * Should return: [
     *     \Viserio\Component\Routing\Matcher\StaticMatcher{ $value => 'user' },
     *     \Viserio\Component\Routing\Matcher\ParameterMatcher{ $name => 'id', $match => '[0-9]+' },
     *     \Viserio\Component\Routing\Matcher\StaticMatcher{ $value => 'create' },
     * ]
     *
     * @param string   $route
     * @param string[] $conditions
     *
     * @throws \Viserio\Component\Contract\Routing\Exception\InvalidArgumentException
     * @throws \Viserio\Component\Contract\Routing\Exception\InvalidRoutePatternException
     *
     * @return array
     */
    public static function parse(string $route, array $conditions): array
    {
        if (\strlen($route) > 1 && $route[0] !== '/') {
            throw new InvalidRoutePatternException(\sprintf(
                'Invalid route pattern: non-root route must be prefixed with \'/\', \'%s\' given.',
                $route
            ));
        }

        $segments        = [];
        $matches         = [];
        $names           = [];
        $patternSegments = \explode('/', $route);

        \array_shift($patternSegments);

        foreach ($patternSegments as $key => $patternSegment) {
            if (self::matchRouteParameters($route, $patternSegment, $conditions, $matches, $names)) {
                $segments[] = new ParameterMatcher(
                    $names,
                    self::generateRegex($matches, $conditions)
                );
            } else {
                $segments[] = new StaticMatcher($patternSegment);
            }
        }

        return $segments;
    }

    /**
     * Validate and match uri parameters.
     *
     * @param string $route
     * @param string $patternSegment
     * @param array  $conditions
     * @param array  $matches
     * @param array  $names
     *
     * @throws \Viserio\Component\Contract\Routing\Exception\InvalidRoutePatternException
     *
     * @return bool
     */
    private static function matchRouteParameters(
        string $route,
        string $patternSegment,
        array &$conditions,
        array &$matches,
        array &$names
    ): bool {
        $matchedParameter = false;
        $names            = [];
        $matches          = [];
        $current          = '';
        $inParameter      = false;

        foreach (\str_split($patternSegment) as $character) {
            if ($inParameter) {
                if ($character === '}') {
                    if (\strpos($current, ':') !== false) {
                        $regex                = \substr($current, \strpos($current, ':') + 1);
                        $current              = \substr($current, 0, \strpos($current, ':'));
                        $conditions[$current] = $regex;
                    }

                    $matches[]        = [self::PARAMETER_PART, $current];
                    $names[]          = $current;
                    $current          = '';
                    $inParameter      = false;
                    $matchedParameter = true;

                    continue;
                }

                if ($character === '{') {
                    throw new InvalidRoutePatternException(\sprintf(
                        'Invalid route uri; Cannot contain nested \'{\', \'%s\' given.',
                        $route
                    ));
                }
            } else {
                if ($character === '{') {
                    $matches[]   = [self::STATIC_PART, $current];
                    $current     = '';
                    $inParameter = true;

                    continue;
                }

                if ($character === '}') {
                    throw new InvalidRoutePatternException(\sprintf(
                        'Invalid route uri; Cannot contain \'}\' before opening \'{\', \'%s\' given.',
                        $route
                    ));
                }
            }

            $current .= $character;
        }

        if ($inParameter) {
            throw new InvalidRoutePatternException(\sprintf(
                'Invalid route uri: cannot contain \'{\' without closing \'}\', \'%s\' given',
                $route
            ));
        }

        if ($current !== '') {
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
    private static function generateRegex(array $matches, array $parameterPatterns): string
    {
        $regex = '/^';

        foreach ($matches as $match) {
            [$type, $part] = $match;

            if ($type === self::STATIC_PART) {
                $regex .= \preg_quote($part, '/');
            } else {
                // Parameter, $part is the parameter name
                $pattern = $parameterPatterns[$part] ?? Pattern::ANY;
                $regex .= '(' . $pattern . ')';
            }
        }

        return $regex . '$/';
    }
}
