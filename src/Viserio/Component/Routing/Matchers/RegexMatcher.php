<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Matchers;

use RuntimeException;
use Viserio\Component\Contracts\Routing\SegmentMatcher as SegmentMatcherContract;
use Viserio\Component\Support\VarExporter;

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
     * @var array
     */
    protected $parameterKeyGroupMap;

    /**
     * Create a new regex segment matcher instance.
     *
     * @param string    $regex
     * @param array|int $parameterKeyGroupMap
     */
    public function __construct(string $regex, $parameterKeyGroupMap)
    {
        if ((mb_strpos($regex, '/^(') !== false && mb_strpos($regex, ')$/') !== false) ||
            (mb_strpos($regex, '/^') !== false && mb_strpos($regex, '$/') !== false)
        ) {
            $this->regex = $regex;
        } else {
            $this->regex = '/^(' . $regex . ')$/';
        }

        $map = is_array($parameterKeyGroupMap) ? $parameterKeyGroupMap : [$parameterKeyGroupMap => 0];

        $this->parameterKeyGroupMap = $map;
        $this->parameterKeys        = array_keys($map);
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

        foreach ($this->parameterKeyGroupMap as $parameterKey => $group) {
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

        if (! method_exists($matcher, 'getParameterKeyGroupMap')) {
            throw new RuntimeException(sprintf('%s::getParameterKeyGroupMap is needed for this function.', get_class($this)));
        }

        $this->parameterKeyGroupMap += $matcher->getParameterKeyGroupMap();
    }

    /**
     * {@inheritdoc}
     */
    protected function getMatchHash(): string
    {
        return $this->regex;
    }
}
