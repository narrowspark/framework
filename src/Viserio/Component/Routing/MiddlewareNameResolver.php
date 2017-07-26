<?php
declare(strict_types=1);
namespace Viserio\Component\Routing;

class MiddlewareNameResolver
{
    /**
     * Resolve the middleware name to a class name(s).
     *
     * @param string $name
     * @param array  $map
     * @param array  $middlewareGroups
     * @param array  $disabledMiddlewares
     *
     * @return array|string
     */
    public static function resolve(string $name, array $map, array $middlewareGroups, array $disabledMiddlewares)
    {
        if (isset($disabledMiddlewares[$name]) || \in_array($name, $disabledMiddlewares, true)) {
            return [];
        }

        if (isset($middlewareGroups[$name])) {
            return self::parseMiddlewareGroup($name, $map, $middlewareGroups, $disabledMiddlewares);
        }

        return $map[$name] ?? $name;
    }

    /**
     * Parse the middleware group and format it for usage.
     *
     * @param string $name
     * @param array  $map
     * @param array  $middlewareGroups
     * @param array  $disabledMiddlewares
     *
     * @return array
     */
    protected static function parseMiddlewareGroup(string $name, array $map, array $middlewareGroups, array $disabledMiddlewares): array
    {
        $results = [];

        foreach ($middlewareGroups[$name] as $middleware) {
            $name = \is_object($middleware) ? \get_class($middleware) : $middleware;

            if (isset($disabledMiddlewares[$name]) || \in_array($name, $disabledMiddlewares, true)) {
                continue;
            }

            // If the middleware is another middleware group we will pull in the group and
            // merge its middleware into the results. This allows groups to conveniently
            // reference other groups without needing to repeat all their middlewares.
            if (\is_string($middleware) && isset($middlewareGroups[$middleware])) {
                $results = \array_merge(
                    $results,
                    self::parseMiddlewareGroup($middleware, $map, $middlewareGroups, $disabledMiddlewares)
                );

                continue;
            }

            // If this middleware is actually a route middleware, we will extract the full
            // class name out of the middleware list now. Then we'll add the parameters
            // back onto this class' name so the pipeline will properly extract them.
            if (isset($map[$name])) {
                $middleware = $map[$name];
            }

            $results[] = $middleware;
        }

        return $results;
    }
}
