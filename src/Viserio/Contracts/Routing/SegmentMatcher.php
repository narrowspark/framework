<?php
declare(strict_types=1);
namespace Viserio\Contracts\Routing;

interface SegmentMatcher
{
    const SEGMENT_PLACEHOLDER = '{segment}';

    /**
     * Return all set parameters keys.
     *
     * @return int[]
     */
    public function getParameterKeys(): array;

    /**
     * Get a metched parameter key back,
     *
     * @param string   $segmentVariable
     * @param int|null $uniqueKey
     *
     * @return string[]
     */
    public function getMatchedParameterExpressions(string $segmentVariable, int $uniqueKey = null): array;

    /**
     * Merge parameters keys from same matcher.
     *
     * @param \Viserio\Contracts\Routing\SegmentMatcher $matcher
     */
    public function mergeParameterKeys(SegmentMatcher $matcher);

    /**
     * Get a ready to use condition expression from a segment.
     *
     * @param string   $segmentVariable
     * @param int|null $uniqueKey
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
