<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Matcher;

use Viserio\Component\Contracts\Routing\Exception\InvalidArgumentException;
use Viserio\Component\Contracts\Routing\SegmentMatcher as SegmentMatcherContract;

abstract class AbstractMatcher implements SegmentMatcherContract
{
    /**
     * Stores all parameters keys.
     *
     * @var array
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
        return \array_fill_keys($this->parameterKeys, $segmentVariable);
    }

    /**
     * {@inheritdoc}
     */
    public function mergeParameterKeys(SegmentMatcherContract $matcher): void
    {
        if ($matcher->getHash() !== $this->getHash()) {
            throw new InvalidArgumentException(
                \sprintf(
                    'Cannot merge parameters: Matcher\'s must be equivalent, [%s] expected, [%s] given.',
                    $matcher->getHash(),
                    $this->getHash()
                )
            );
        }

        $this->parameterKeys = \array_unique(
            \array_merge($this->parameterKeys, $matcher->getParameterKeys()),
            SORT_NUMERIC
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getHash(): string
    {
        return \get_class($this) . ':' . $this->getMatchHash();
    }

    /**
     * Returns a unique hash for the matching criteria of the segment.
     *
     * @return string
     */
    abstract protected function getMatchHash(): string;
}
