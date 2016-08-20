<?php
declare(strict_types=1);
namespace Viserio\Routing\Segments;

use Viserio\Routing\Matchers\RegexMatcher;

class ParameterSegment
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
     * [getMatcher description]
     *
     * @param array &$parameterIndexNameMap
     *
     * @return \Viserio\Routing\Matchers\RegexMatcher
     */
    public function getMatcher(array &$parameterIndexNameMap): RegexMatcher
    {
        $parameterKey = empty($parameterIndexNameMap) ? 0 : max(array_keys($parameterIndexNameMap)) + 1;
        $parameterKeyGroupMap = [];
        $group = 0;

        foreach ($this->names as $name) {
            $parameterIndexNameMap[$parameterKey] = $name;
            $parameterKeyGroupMap[$parameterKey] = $group++;
            ++$parameterKey;
        }

        return new RegexMatcher($this->regex, $parameterKeyGroupMap);
    }
}
