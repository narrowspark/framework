<?php
declare(strict_types=1);
namespace Viserio\Contracts\Routing;

interface SegmentMatcher
{
     /**
     * Return all set parameters keys.
     *
     * @return int[]
     */
    public function getParameterKeys(): array;

    /**
     * Get a metched parameter key back,
     *
     * @param string $segmentVariable
     * @param int    $uniqueKey
     *
     * @return string[]
     */
    public function getMatchedParameterExpressions(string $segmentVariable, int $uniqueKey): array;

     /**
      * Merge parameters keys from same matcher.
      *
      * @param \Viserio\Contracts\Routing\SegmentMatcher $matcher
      */
    public function mergeParameterKeys(SegmentMatcher $matcher);

    /**
     * Get a ready to use condition expression from a segment.
     *
     * @param string $segmentVariable
     * @param int    $uniqueKey
     *
     * @return string
     */
    public function getConditionExpression(string $segmentVariable, int $uniqueKey): string;

    /**
     * Returns a unique hash for the matching criteria of the segment.
     *
     * @return string
     */
    public function getMatchHash(): string;
}
