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

use Viserio\Component\Routing\Matcher\ParameterMatcher;
use Viserio\Contract\Routing\Route as RouteContract;
use Viserio\Contract\Routing\SegmentMatcher as SegmentMatcherContract;

final class RouteTreeBuilder
{
    /**
     * Creates a route tree from the supplied routes.
     *
     * @param \Viserio\Contract\Routing\Route[] $routes
     *
     * @return array
     */
    public function build(array $routes): array
    {
        $rootRouteData = null;
        $nodes = [];
        $groupedRoutes = [];

        foreach ($routes as $route) {
            $groupedRoutes[\count($route->getSegments())][] = $route;
        }

        if (isset($groupedRoutes[0])) {
            $rootRouteData = new MatchedRouteDataMap();
            $rootRouteData->addRoute($groupedRoutes[0][0], []);

            unset($groupedRoutes[0]);
        }

        foreach ($groupedRoutes as $segmentDepth => $group) {
            $groupNodes = [];

            foreach ($group as $route) {
                $parameterIndexNameMap = [];
                $segments = $route->getSegments();
                $segmentMatcher = $this->getMatcher(\array_shift($segments), $parameterIndexNameMap);
                $firstSegmentHash = $segmentMatcher->getHash();

                if (! isset($groupNodes[$firstSegmentHash])) {
                    $groupNodes[$firstSegmentHash] = new RouteTreeNode(
                        [0 => $segmentMatcher],
                        $segmentDepth === 1 ? new MatchedRouteDataMap() : new ChildrenNodeCollection()
                    );
                }

                $this->addRouteToNode($groupNodes[$firstSegmentHash], $route, $segments, 1, $parameterIndexNameMap);
            }

            $nodes[$segmentDepth] = new ChildrenNodeCollection($groupNodes);
        }

        return [$rootRouteData, $nodes];
    }

    /**
     * Adds a route to the node tree.
     *
     * @param \Viserio\Component\Routing\TreeGenerator\RouteTreeNode $node
     * @param \Viserio\Contract\Routing\Route                        $route
     * @param array                                                  $segments
     * @param int                                                    $segmentDepth
     * @param array                                                  $parameterIndexNameMap
     *
     * @throws \Viserio\Contract\Routing\Exception\RuntimeException
     *
     * @return void
     */
    private function addRouteToNode(
        RouteTreeNode $node,
        RouteContract $route,
        array $segments,
        int $segmentDepth,
        array $parameterIndexNameMap
    ): void {
        if (\count($segments) === 0) {
            $node->getContents()->addRoute($route, $parameterIndexNameMap);

            return;
        }

        $childSegmentMatcher = $this->getMatcher(\array_shift($segments), $parameterIndexNameMap);

        if ($node->getContents()->hasChildFor($childSegmentMatcher)) {
            $child = $node->getContents()->getChild($childSegmentMatcher);
        } else {
            $child = new RouteTreeNode(
                [$segmentDepth => $childSegmentMatcher],
                \count($segments) === 0 ? new MatchedRouteDataMap() : new ChildrenNodeCollection()
            );
            $node->getContents()->addChild($child);
        }

        $this->addRouteToNode($child, $route, $segments, $segmentDepth + 1, $parameterIndexNameMap);
    }

    /**
     * Get the right Matcher.
     *
     * @param \Viserio\Component\Routing\Matcher\ParameterMatcher|\Viserio\Contract\Routing\SegmentMatcher $firstSegment
     * @param array                                                                                        $parameterIndexNameMap
     *
     * @return \Viserio\Contract\Routing\SegmentMatcher
     */
    private function getMatcher($firstSegment, array &$parameterIndexNameMap): SegmentMatcherContract
    {
        if ($firstSegment instanceof ParameterMatcher) {
            return $firstSegment->getMatcher($parameterIndexNameMap);
        }

        return $firstSegment;
    }
}
