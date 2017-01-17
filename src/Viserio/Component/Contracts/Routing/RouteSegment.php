<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Routing;

interface RouteSegment
{
    /**
     * Returns an equivalent segment matcher and adds the parameters to the map.
     *
     * @param array $parameterIndexNameMap
     *
     * @return \Viserio\Component\Contracts\Routing\SegmentMatcher
     */
    public function getMatcher(array &$parameterIndexNameMap): SegmentMatcher;
}
