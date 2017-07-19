<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Routing;

interface SegmentMatcher
{
    /**
     * Return all set parameters keys.
     *
     * @return int[]
     */
    public function getParameterKeys(): array;

    /**
     * Get a matched parameter key back,.
     *
     * @param string   $segmentVariable
     * @param null|int $uniqueKey
     *
     * @return string[]
     */
    public function getMatchedParameterExpressions(string $segmentVariable, int $uniqueKey = null): array;

    /**
     * Merge parameters keys from same matcher.
     *
     * @param \Viserio\Component\Contracts\Routing\SegmentMatcher $matcher
     */
    public function mergeParameterKeys(SegmentMatcher $matcher);

    /**
     * Get a ready to use condition expression from a segment.
     *
     * @param string   $segmentVariable
     * @param null|int $uniqueKey
     *
     * @return string
     */
    public function getConditionExpression(string $segmentVariable, int $uniqueKey = null): string;

    /**
     * Returns a unique hash for the segment matcher.
     *
     * @return string
     */
    public function getHash(): string;
}
