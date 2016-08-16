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
    public function getMatchedParameterExpressions(string $segmentVariable, string $uniqueKey = null): array
    {
        return array_fill_keys($this->parameterKeys, $segmentVariable);
    }

    /**
     * {@inheritdoc}
     */
    public function mergeParameterKeys(SegmentMatcherContract $matcher)
    {
        if ($matcher->getHash() !== $this->getHash()) {
            throw new RuntimeException(
                sprintf(
                    'Cannot merge parameters: matchers must be equivalent, \'%s\' expected, \'%s\' given.',
                    $matcher->getHash(),
                    $this->getHash()
                )
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
    public function getHash(): string
    {
        return get_class($this) . ':' . $this->getMatchHash();
    }

    /**
     * Returns a unique hash for the matching criteria of the segment.
     *
     * @return string
     */
    abstract protected function getMatchHash(): string;
}
