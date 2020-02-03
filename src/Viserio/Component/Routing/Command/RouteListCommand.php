<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Routing\Command;

use Viserio\Component\Console\Command\AbstractCommand;
use Viserio\Contract\Routing\Route as RouteContract;
use Viserio\Contract\Routing\Router as RouterContract;

class RouteListCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected static $defaultName = 'route:table';

    /**
     * {@inheritdoc}
     */
    protected $signature = 'route:table
        [--method= : Filter the routes by method.]
        [--name= : Filter the routes by name.]
        [--path= : Filter the routes by path.]
        [-r|--reverse : Reverse the ordering of the routes.]
        [--sort=uri : The column (host, method, uri, name, action) to sort by.]
    ';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Table of all registered routes';

    /**
     * The table headers for the command.
     *
     * @var array
     */
    protected static $headers = ['method', 'uri', 'name', 'controller', 'action'];

    /**
     * An array of all the registered routes.
     *
     * @var array
     */
    protected $routes = [];

    /**
     * Create a new route command instance.
     *
     * @param \Viserio\Contract\Routing\Router $router
     */
    public function __construct(RouterContract $router)
    {
        parent::__construct();

        $this->routes = $router->getRoutes()->getRoutes();
    }

    /**
     * {@inheritdoc}
     */
    public function handle(): int
    {
        if (\count($this->routes) === 0) {
            $this->error("Your application doesn't have any routes.");

            return 1;
        }

        $this->table(self::$headers, $this->getRoutes());

        return 0;
    }

    /**
     * Compile the routes into a displayable format.
     *
     * @return array
     */
    protected function getRoutes(): array
    {
        $routes = [];

        foreach ($this->routes as $route) {
            if (($routeInfo = $this->getRouteInformation($route)) !== null) {
                $routes[] = $routeInfo;
            }
        }

        if ($sort = $this->option('sort')) {
            $routes = self::sort($routes, static function ($route) use ($sort) {
                return $route[$sort];
            });
        }

        if ($this->option('reverse')) {
            $routes = \array_reverse($routes);
        }

        return \array_filter($routes);
    }

    /**
     * Get the route information for a given route.
     *
     * @param \Viserio\Contract\Routing\Route $route
     *
     * @return null|array
     */
    protected function getRouteInformation(RouteContract $route): ?array
    {
        $actions = \explode('@', $route->getActionName());

        return $this->filterRoute([
            'method' => $route->getMethods(),
            'uri' => $route->getUri(),
            'name' => \is_string($route->getName()) ? "<fg=green>{$route->getName()}</>" : '-',
            'controller' => isset($actions[0]) ? "<fg=cyan>{$actions[0]}</>" : '-',
            'action' => isset($actions[1]) ? "<fg=red>{$actions[1]}</>" : '-',
        ]);
    }

    /**
     * Filter the route by URI and / or name.
     *
     * @param array $route
     *
     * @return null|array
     */
    protected function filterRoute(array $route): ?array
    {
        $isNotName = ($this->option('name') && \strpos($route['name'], $this->option('name')) === false);
        $isNotPath = ($this->option('path') && \strpos($route['uri'], $this->option('path')) === false);
        $isNotMethod = ($this->option('method') && \in_array(\strtoupper($this->option('method')), $route['method'], true) === false);

        if ($isNotName || $isNotPath || $isNotMethod) {
            return null;
        }

        $route['method'] = \implode('|', $route['method']);

        return $route;
    }

    /**
     * Sort the array using the given callback.
     *
     * @param array    $array
     * @param callable $callback
     *
     * @return array
     */
    protected static function sort(array $array, callable $callback): array
    {
        $results = [];

        foreach ($array as $key => $value) {
            $results[$key] = $callback($value, $key);
        }

        \asort($results, \SORT_REGULAR);

        foreach (\array_keys($results) as $key) {
            $results[$key] = $array[$key];
        }

        return $results;
    }
}
