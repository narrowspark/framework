<?php
declare(strict_types=1);
namespace Viserio\Routing\Matchers;

use RuntimeException;
use Viserio\Contracts\Routing\SegmentMatcher as SegmentMatcherContract;

abstract class AbstractMatcher implements SegmentMatcherContract
{
    /**
     * Stores all parameters keys.
     *
     * @var int[]
     */
    protected $parameterKeys = [];

    /**
     * {@inheritdoc}
     */
    public function getParameterKeys(): array
    {
        return $this->parameterKeys;
    }

    /**
     * {@inheritdoc}
     */
    public function getMatchedParameterExpressions(string $segmentVariable, int $uniqueKey = null): array
    {
        return array_fill_keys($this->parameterKeys, $segmentVariable);
    }

    /**
     * {@inheritdoc}
     */
    public function mergeParameterKeys(SegmentMatcherContract $matcher)
    {
        if($matcher->getMatchHash() !== $this->getMatchHash()) {
            throw new RuntimeException(
                sprintf('Cannot merge parameters: matchers must be equivalent, \'%s\' expected, \'%s\' given', get_class($matcher), $this->getMatchHash())
            );
        }

        $this->parameterKeys = array_unique(
            array_merge($this->parameterKeys, $matcher->getParameterKeys()),
            SORT_NUMERIC
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getMatchHash(): string
    {
        return uniqid(get_called_class(), true);
    }
}
