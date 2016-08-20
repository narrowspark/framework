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
    protected $httpMethodRouteDataMap = [];

    /**
     * @var array|null
     */
    protected $defaultRouteData = null;

    /**
     * Create a new child node collection instance.
     *
     * @param array $children
     */
    public function __construct(array $httpMethodRouteDataMap = [], array $defaultRouteData = null)
    {
        $this->httpMethodRouteDataMap = $httpMethodRouteDataMap;
        $this->defaultRouteData = $defaultRouteData;
    }

    /**
     * @return array
     */
    public function getHttpMethodRouteDataMap()
    {
        return $this->httpMethodRouteDataMap;
    }

    /**
     * @return array|null
     */
    public function getAllowedHttpMethods()
    {
        if ($this->hasDefaultRouteData()) {
            return 'GET';
        }

        $allowedHttpMethods = [];

        foreach ($this->httpMethodRouteDataMap as $item) {
            foreach ($item[0] as $method) {
                $allowedHttpMethods[$method] = true;
            }
        }

        return array_keys($allowedHttpMethods);
    }

    /**
     * @return MatchedRouteData|null
     */
    public function getDefaultRouteData()
    {
        return $this->defaultRouteData;
    }

    /**
     * @return bool
     */
    public function hasDefaultRouteData()
    {
        return $this->defaultRouteData !== null;
    }

    /**
     * @return bool
     */
    public function isEmpty()
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
