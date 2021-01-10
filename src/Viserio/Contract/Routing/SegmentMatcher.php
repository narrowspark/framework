<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Contract\Routing;

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
     * @return string[]
     */
    public function getMatchedParameterExpressions(string $segmentVariable, ?int $uniqueKey = null): array;

    /**
     * Merge parameters keys from same matcher.
     *
     * @param self $matcher
     */
    public function mergeParameterKeys(SegmentMatcher $matcher): void;

    /**
     * Get a ready to use condition expression from a segment.
     */
    public function getConditionExpression(string $segmentVariable, ?int $uniqueKey = null): string;

    /**
     * Returns a unique hash for the segment matcher.
     */
    public function getHash(): string;
}
