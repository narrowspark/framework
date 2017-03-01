<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Resolver;

class MiddlewareName
{
    /**
     * Resolve the middleware name to a class name(s).
     *
     * @param string $name
     * @param array  $map
     * @param array  $middlewareGroups
     *
     * @return string|array
     */
    public static function resolve(string $name, array $map, array $middlewareGroups)
    {
        if (isset($middlewareGroups[$name])) {
            return self::parseMiddlewareGroup($name, $map, $middlewareGroups);
        }

        return $map[$name] ?? $name;
    }

    /**
     * Parse the middleware group and format it for usage.
     *
     * @param string $name
     * @param array  $map
     * @param array  $middlewareGroups
     *
     * @return array
     */
    protected static function parseMiddlewareGroup(string $name, array $map, array $middlewareGroups): array
    {
        $results = [];

        foreach ($middlewareGroups[$name] as $middleware) {
            // If the middleware is another middleware group we will pull in the group and
            // merge its middleware into the results. This allows groups to conveniently
            // reference other groups without needing to repeat all their middlewares.
            if (isset($middlewareGroups[$middleware])) {
                $results = array_merge(
                    $results,
                    self::parseMiddlewareGroup($middleware, $map, $middlewareGroups)
                );

                continue;
            }

            // If this middleware is actually a route middleware, we will extract the full
            // class name out of the middleware list now. Then we'll add the parameters
            // back onto this class' name so the pipeline will properly extract them.
            if (isset($map[$middleware])) {
                $middleware = $map[$middleware];
            }

            $results[] = $middleware;
        }

        return $results;
    }
}
