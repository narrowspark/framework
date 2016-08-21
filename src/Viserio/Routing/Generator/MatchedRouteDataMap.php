<?php
declare(strict_types=1);
namespace Viserio\Routing\Generator;

use Viserio\Contracts\Routing\NodeContents as NodeContentsContract;
use Viserio\Contracts\Routing\Route as RouteContract;

class MatchedRouteDataMap implements NodeContentsContract
{
    /**
     * @var array
     */
    protected $httpMethodRouteMap = [];

    /**
     * @var array|null
     */
    protected $defaultRouteData = null;

    /**
     * Create a new child node collection instance.
     *
     * @param array      $httpMethodRouteMap
     * @param array|null $defaultRouteData
     */
    public function __construct(array $httpMethodRouteMap = [], array $defaultRouteData = null)
    {
        $this->httpMethodRouteMap = $httpMethodRouteMap;
        $this->defaultRouteData = $defaultRouteData;
    }

    /**
     * @return array
     */
    public function getHttpMethodRouteDataMap(): array
    {
        return $this->httpMethodRouteMap;
    }

    /**
     * Get all allowed http methods.
     *
     * @return array|null
     */
    public function getAllowedHttpMethods()
    {
        if ($this->hasDefaultRouteData()) {
            return 'GET';
        }

        $allowedHttpMethods = [];

        foreach ($this->httpMethodRouteMap as $item) {
            foreach ($item[0] as $method) {
                $allowedHttpMethods[$method] = true;
            }
        }

        return array_keys($allowedHttpMethods);
    }

    /**
     * Get the default data.
     *
     * @return array|null
     */
    public function getDefaultRouteData()
    {
        return $this->defaultRouteData;
    }

    /**
     * Check if route has default data.
     *
     * @return bool
     */
    public function hasDefaultRouteData(): bool
    {
        return $this->defaultRouteData !== null;
    }

    /**
     * Check if route is empty.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->defaultRouteData === null && empty($this->httpMethodRouteDataMap);
    }

    /**
     * Adds the supplied route to the matched route data map
     *
     * @param \Viserio\Contracts\Routing\Route $route
     * @param array                            $parameterIndexNameMap
     */
    public function addRoute(RouteContract $route, array $parameterIndexNameMap)
    {
        if (count($route->getMethods()) === 1 && in_array('ANY', $route->getMethods())) {
            $this->defaultRouteData = [$parameterIndexNameMap, $route->getParameters()];
        } else {
            $this->httpMethodRouteDataMap[] = [$route->getMethods(), [$parameterIndexNameMap, $route->getParameters()]];
        }
    }
}
