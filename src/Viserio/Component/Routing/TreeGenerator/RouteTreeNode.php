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

namespace Viserio\Component\Routing\TreeGenerator;

use Viserio\Contract\Routing\Exception\RuntimeException;
use Viserio\Contract\Routing\SegmentMatcher as SegmentMatcherContract;

final class RouteTreeNode
{
    /**
     * All segment matcher.
     *
     * @var \Viserio\Contract\Routing\SegmentMatcher[]
     */
    private $matchers;

    /**
     * Actual route content.
     *
     * @var \Viserio\Component\Routing\TreeGenerator\ChildrenNodeCollection|\Viserio\Component\Routing\TreeGenerator\MatchedRouteDataMap
     */
    private $contents;

    /**
     * Create a new child node collection instance.
     *
     * @param \Viserio\Contract\Routing\SegmentMatcher[]                                                                                   $matchers
     * @param \Viserio\Component\Routing\TreeGenerator\ChildrenNodeCollection|\Viserio\Component\Routing\TreeGenerator\MatchedRouteDataMap $contents
     *
     * @throws \Viserio\Contract\Routing\Exception\RuntimeException
     */
    public function __construct(array $matchers, $contents)
    {
        if (\count($matchers) === 0) {
            throw new RuntimeException(\sprintf('Cannot construct [%s], matchers must not be empty.', __CLASS__));
        }

        $this->matchers = $matchers;
        $this->contents = $contents;
    }

    /**
     * Get all matchers from array.
     *
     * @return \Viserio\Contract\Routing\SegmentMatcher[]
     */
    public function getMatchers(): array
    {
        return $this->matchers;
    }

    /**
     * Get actual route content.
     *
     * @return \Viserio\Component\Routing\TreeGenerator\ChildrenNodeCollection|\Viserio\Component\Routing\TreeGenerator\MatchedRouteDataMap
     */
    public function getContents()
    {
        return $this->contents;
    }

    /**
     * Get the first matcher from array.
     */
    public function getFirstMatcher(): SegmentMatcherContract
    {
        return $this->matchers[\min(\array_keys($this->matchers))];
    }

    /**
     * Check if content is a leaf node.
     */
    public function isLeafNode(): bool
    {
        return $this->contents instanceof MatchedRouteDataMap;
    }

    /**
     * Check if content is a parent node.
     */
    public function isParentNode(): bool
    {
        return $this->contents instanceof ChildrenNodeCollection;
    }

    /**
     * Update RouteTreeNode class.
     *
     * @param \Viserio\Component\Routing\TreeGenerator\ChildrenNodeCollection|\Viserio\Component\Routing\TreeGenerator\MatchedRouteDataMap $contents
     *
     * @throws \RuntimeException
     *
     * @return self
     */
    public function update(array $matchers, $contents): RouteTreeNode
    {
        if ($this->matchers === $matchers && $this->contents === $contents) {
            return $this;
        }

        $clone = clone $this;
        $clone->matchers = $matchers;
        $clone->contents = $contents;

        return $clone;
    }
}
