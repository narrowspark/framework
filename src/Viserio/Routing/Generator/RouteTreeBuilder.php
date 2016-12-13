<?php
declare(strict_types=1);
namespace Viserio\Routing\Generator;

use Viserio\Contracts\Routing\Route as RouteContract;
use Viserio\Contracts\Routing\RouteSegment as RouteSegmentContract;
use Viserio\Contracts\Routing\SegmentMatcher as SegmentMatcherContract;

class RouteTreeBuilder
{
    /**
     * Creates a route tree from the supplied routes.
     *
     * @param \Viserio\Contracts\Routing\Route[] $routes
     *
     * @return array
     */
    public function build(array $routes): array
    {
        $rootRouteData = null;
        $nodes = [];
        $groupedRoutes = [];

        foreach ($routes as $route) {
            $groupedRoutes[count($route->getSegments())][] = $route;
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
                $segmentMatcher = $this->getMatcher(array_shift($segments), $parameterIndexNameMap);
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
     * @param \Viserio\Routing\Generator\RouteTreeNode $node
     * @param \Viserio\Contracts\Routing\Route         $route
     * @param array                                    $segments
     * @param int                                      $segmentDepth
     * @param array                                    $parameterIndexNameMap
     */
    protected function addRouteToNode(
        RouteTreeNode $node,
        RouteContract $route,
        array $segments,
        int $segmentDepth,
        array $parameterIndexNameMap
    ) {
        if (empty($segments)) {
            $node->getContents()->addRoute($route, $parameterIndexNameMap);

            return null;
        }

        $childSegmentMatcher = $this->getMatcher(array_shift($segments), $parameterIndexNameMap);

        if ($node->getContents()->hasChildFor($childSegmentMatcher)) {
            $child = $node->getContents()->getChild($childSegmentMatcher);
        } else {
            $child = new RouteTreeNode(
                [
                    $segmentDepth => $childSegmentMatcher,
                ],
                empty($segments) ? new MatchedRouteDataMap() : new ChildrenNodeCollection()
            );
            $node->getContents()->addChild($child);
        }

        $this->addRouteToNode($child, $route, $segments, $segmentDepth + 1, $parameterIndexNameMap);
    }

    /**
     * Get the right Matcher.
     *
     * @param object $firstSegment
     * @param array  &$parameterIndexNameMap
     *
     * @return \Viserio\Contracts\Routing\SegmentMatcher
     */
    private function getMatcher($firstSegment, array &$parameterIndexNameMap): SegmentMatcherContract
    {
        if ($firstSegment instanceof RouteSegmentContract) {
            return $firstSegment->getMatcher($parameterIndexNameMap);
        }

        return $firstSegment;
    }
}
