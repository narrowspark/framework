<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Matcher;

use Viserio\Component\Contract\Routing\SegmentMatcher as SegmentMatcherContract;

class ParameterMatcher
{
    /**
     * Segments names.
     *
     * @var array
     */
    protected $names;

    /**
     * A regex string.
     *
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
        $this->names = (array) $names;
        $this->regex = $regex;
    }

    /**
     * Returns an equivalent segment matcher and adds the parameters to the map.
     *
     * @param array $parameterIndexNameMap
     *
     * @return \Viserio\Component\Contract\Routing\SegmentMatcher
     */
    public function getMatcher(array &$parameterIndexNameMap): SegmentMatcherContract
    {
        $parameterKey         = \count($parameterIndexNameMap) === 0 ? 0 : \max(\array_keys($parameterIndexNameMap)) + 1;
        $parameterKeyGroupMap = [];
        $group                = 0;

        foreach ($this->names as $name) {
            $parameterIndexNameMap[$parameterKey] = $name;
            $parameterKeyGroupMap[$parameterKey]  = $group++;
            $parameterKey++;
        }

        return new RegexMatcher($this->regex, $parameterKeyGroupMap);
    }
}
