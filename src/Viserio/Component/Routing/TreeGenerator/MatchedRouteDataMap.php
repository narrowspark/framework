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

namespace Viserio\Component\Routing\TreeGenerator;

use Viserio\Contract\Routing\Route as RouteContract;

final class MatchedRouteDataMap
{
    /** @var array */
    private $httpMethodRouteMap = [];

    /**
     * Create a new matched route data map instance.
     *
     * @param array $httpMethodRouteMap
     */
    public function __construct(array $httpMethodRouteMap = [])
    {
        $this->httpMethodRouteMap = $httpMethodRouteMap;
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
     * @return array
     */
    public function allowedHttpMethods(): array
    {
        $allowedHttpMethods = [];

        foreach ($this->httpMethodRouteMap as $item) {
            foreach ($item[0] as $method) {
                $allowedHttpMethods[] = $method;
            }
        }

        return \array_values($allowedHttpMethods);
    }

    /**
     * Adds the supplied route to the matched route data map.
     *
     * @param \Viserio\Contract\Routing\Route $route
     * @param array                           $parameterIndexNameMap
     */
    public function addRoute(RouteContract $route, array $parameterIndexNameMap): void
    {
        $this->httpMethodRouteMap[] = [$route->getMethods(), [$parameterIndexNameMap, $route->getIdentifier()]];
    }
}
