<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Matcher;

class CompoundMatcher extends AbstractMatcher
{
    /**
     * A array of all SegmentMatcher.
     *
     * @var \Viserio\Component\Contracts\Routing\SegmentMatcher[]
     */
    protected $matchers;

    /**
     * Create a new compound matcher instance.
     *
     * @param array $matchers
     */
    public function __construct(array $matchers)
    {
        $parameterKeys = [];

        foreach ($matchers as $matcher) {
            $parameterKeys = \array_merge($parameterKeys, $matcher->getParameterKeys());
        }

        $this->parameterKeys = $parameterKeys;
        $this->matchers      = $matchers;
    }

    /**
     * {@inheritdoc}
     */
    public function getConditionExpression(string $segmentVariable, int $uniqueKey = null): string
    {
        $conditions = [];

        foreach ($this->matchers as $key => $matcher) {
            $conditions[] = $matcher->getConditionExpression($segmentVariable, $uniqueKey + $key);
        }

        return \implode(' && ', $conditions);
    }

    /**
     * {@inheritdoc}
     */
    public function getMatchedParameterExpressions(string $segmentVariable, int $uniqueKey = null): array
    {
        $expressions = [];

        foreach ($this->matchers as $key => $matcher) {
            $matchedParameterExpressions = $matcher->getMatchedParameterExpressions(
                $segmentVariable,
                $uniqueKey + $key
            );

            foreach ($matchedParameterExpressions as $parameter => $expression) {
                $expressions[$parameter] = $expression;
            }
        }

        return $expressions;
    }

    /**
     * {@inheritdoc}
     */
    protected function getMatchHash(): string
    {
        $hashes = [];

        foreach ($this->matchers as $matcher) {
            $hashes[] = $matcher->getHash();
        }

        return \implode('::', $hashes);
    }
}
