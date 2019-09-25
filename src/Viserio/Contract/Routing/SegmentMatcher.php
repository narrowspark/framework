<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
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
     * @param string   $segmentVariable
     * @param null|int $uniqueKey
     *
     * @return string[]
     */
    public function getMatchedParameterExpressions(string $segmentVariable, int $uniqueKey = null): array;

    /**
     * Merge parameters keys from same matcher.
     *
     * @param self $matcher
     *
     * @return void
     */
    public function mergeParameterKeys(SegmentMatcher $matcher): void;

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
