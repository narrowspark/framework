<?php
declare(strict_types=1);
namespace Viserio\Routing\Matchers;

use Viserio\Routing\{
    Pattern,
    VarExporter
};

class RegexMatcher extends AbstractMatcher
{
    /**
     * Used regex.
     *
     * @var string
     */
    protected $regex;

    /**
     * A group of paramters keys.
     *
     * @var int[]
     */
    protected $parameterKeyGroupMap;

    /**
     * Create a new regex segment matcher instance.
     *
     * @param string $segment
     * @param array  $parameterKeyGroupMap
     */
    public function __construct(string $regex, array $parameterKeyGroupMap)
    {
        $this->regex = Pattern::asRegex($regex);
        $this->parameterKeyGroupMap = $parameterKeyGroupMap;
        $this->parameterKeys = array_keys($parameterKeyGroupMap);
    }

    /**
     * Counted parameters keys.
     *
     * @return int
     */
    public function getGroupCount(): int
    {
        return count(array_unique($this->parameterKeyGroupMap, SORT_NUMERIC));
    }

    /**
     * Retruns the parameters key group array.
     *
     * @return array
     */
    public function getParameterKeyGroupMap(): array
    {
        return $this->parameterKeyGroupMap;
    }

    /**
     * Retruns the used regex.
     *
     * @return string
     */
    public function getRegex(): string
    {
        return $this->regex;
    }

    /**
     * {@inheritdoc}
     */
    public function getConditionExpression(string $segmentVariable, int $uniqueKey = null): string
    {
        return 'preg_match('
            . VarExporter::export($this->regex)
            . ', '
            . $segmentVariable
            . ', '
            . '$matches' . $uniqueKey
            . ')';
    }

    /**
     * {@inheritdoc}
     */
    public function getMatchedParameterExpressions(string $segmentVariable, int $uniqueKey = null): array
    {
        $matches = [];

        foreach($this->parameterKeyGroupMap as $parameterKey => $group) {
            // Use $group + 1 as the first $matches element is the full text that matched,
            // we want the groups
            $matches[$parameterKey] = '$matches' . $uniqueKey . '[' . ($group + 1) . ']';
        }

        return $matches;
    }

    /**
     * {@inheritdoc}
     */
    public function mergeParameterKeys(SegmentMatcherContract $matcher)
    {
        parent::mergeParameterKeys($matcher);

        $this->parameterKeyGroupMap += $matcher->getParameterKeyGroupMap();
    }
}
