<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Routing\TreeGenerator;

use Viserio\Contract\Routing\Route as RouteContract;

final class MatchedRouteDataMap
{
    /** @var array */
    private $httpMethodRouteMap = [];

    /**
     * Create a new matched route data map instance.
     */
    public function __construct(array $httpMethodRouteMap = [])
    {
        $this->httpMethodRouteMap = $httpMethodRouteMap;
    }

    public function getHttpMethodRouteDataMap(): array
    {
        return $this->httpMethodRouteMap;
    }

    /**
     * Get all allowed http methods.
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
     */
    public function addRoute(RouteContract $route, array $parameterIndexNameMap): void
    {
        $this->httpMethodRouteMap[] = [$route->getMethods(), [$parameterIndexNameMap, $route->getIdentifier()]];
    }
}
