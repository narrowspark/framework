<?php
declare(strict_types=1);
namespace Viserio\Routing\Matchers;

use Viserio\Routing\{
    Pattern,
    VarExporter
};

class ParameterMatcher extends RegexMatcher
{
    /**
     * All saved parameter names.
     *
     * @var string[]
     */
    protected $names;

    /**
     * Create a new parameter segment matcher instance.
     *
     * @param string $segment
     * @param array  $parameterKeyGroupMap
     */
    public function __construct($names, string $regex)
    {
        $this->names  = is_array($names) ? $names : [$names];

        parent::__construct($regex, $this->getKeyGroupedMap());
    }

    /**
     * [getKeyGroupMap description]
     *
     * @param  array  $parameterIndexNameMap
     *
     * @return array
     */
    protected function getKeyGroupedMap(array $parameterIndexNameMap): array
    {
        $parameterKey = empty($parameterIndexNameMap) ? 0 : max(array_keys($parameterIndexNameMap)) + 1;
        $parameterKeyGroupMap = [];
        $group = 0;

        foreach($this->names as $name) {
            $parameterKeyGroupMap[$parameterKey] = $group++;
            $parameterKey++;
        }

        return $parameterKeyGroupMap;
    }
}
