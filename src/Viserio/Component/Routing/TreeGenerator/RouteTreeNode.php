<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\TreeGenerator;

use Viserio\Component\Contract\Routing\Exception\RuntimeException;
use Viserio\Component\Contract\Routing\SegmentMatcher as SegmentMatcherContract;

final class RouteTreeNode
{
    /**
     * All segment matcher.
     *
     * @var \Viserio\Component\Contract\Routing\SegmentMatcher[]
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
     * @param \Viserio\Component\Contract\Routing\SegmentMatcher[]                                                                         $matchers
     * @param \Viserio\Component\Routing\TreeGenerator\ChildrenNodeCollection|\Viserio\Component\Routing\TreeGenerator\MatchedRouteDataMap $contents
     *
     * @throws \Viserio\Component\Contract\Routing\Exception\RuntimeException
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
     * @return \Viserio\Component\Contract\Routing\SegmentMatcher[]
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
     * @return \Viserio\Component\Contract\Routing\SegmentMatcher
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
     * @return \Viserio\Component\Routing\TreeGenerator\RouteTreeNode
     */
    public function update(array $matchers, $contents): RouteTreeNode
    {
        if ($this->matchers === $matchers && $this->contents === $contents) {
            return $this;
        }

        $clone           = clone $this;
        $clone->matchers = $matchers;
        $clone->contents = $contents;

        return $clone;
    }
}
