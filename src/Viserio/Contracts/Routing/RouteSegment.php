<?php
declare(strict_types=1);
namespace Viserio\Contracts\Routing;

interface RouteSegment
{
    /**
     * Returns an equivalent segment matcher and adds the parameters to the map.
     *
     * @param array $parameterIndexNameMap
     *
     * @return \Viserio\Contracts\Routing\SegmentMatcher
     */
    public function getMatcher(array &$parameterIndexNameMap);
}
