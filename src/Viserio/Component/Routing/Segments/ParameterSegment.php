<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Segments;

use Viserio\Component\Contracts\Routing\RouteSegment as RouteSegmentContract;
use Viserio\Component\Contracts\Routing\SegmentMatcher as SegmentMatcherContract;
use Viserio\Component\Routing\Matchers\RegexMatcher;

class ParameterSegment implements RouteSegmentContract
{
    /**
     * @var string[]
     */
    protected $names;

    /**
     * @var string
     */
    protected $regex;

    /**
     * Create a new any matcher instance.
     *
     * @param array|string $names
     * @param string       $regex
     */
    public function __construct($names, string $regex)
    {
        $this->names = is_array($names) ? $names : [$names];
        $this->regex = $regex;
    }

    /**
     * {@inheritdoc}
     */
    public function getMatcher(array &$parameterIndexNameMap): SegmentMatcherContract
    {
        $parameterKey         = empty($parameterIndexNameMap) ? 0 : max(array_keys($parameterIndexNameMap)) + 1;
        $parameterKeyGroupMap = [];
        $group                = 0;

        foreach ($this->names as $name) {
            $parameterIndexNameMap[$parameterKey] = $name;
            $parameterKeyGroupMap[$parameterKey]  = $group++;
            ++$parameterKey;
        }

        return new RegexMatcher($this->regex, $parameterKeyGroupMap);
    }
}
