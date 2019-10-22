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
     *
     * @return \Viserio\Contract\Routing\SegmentMatcher
     */
    public function getFirstMatcher(): SegmentMatcherContract
    {
        return $this->matchers[\min(\array_keys($this->matchers))];
    }

    /**
     * Check if content is a leaf node.
     *
     * @return bool
     */
    public function isLeafNode(): bool
    {
        return $this->contents instanceof MatchedRouteDataMap;
    }

    /**
     * Check if content is a parent node.
     *
     * @return bool
     */
    public function isParentNode(): bool
    {
        return $this->contents instanceof ChildrenNodeCollection;
    }

    /**
     * Update RouteTreeNode class.
     *
     * @param array                                                                                                                        $matchers
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
