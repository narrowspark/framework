<?php
declare(strict_types=1);
namespace Viserio\Routing\Generator;

use RuntimeException;
use Viserio\Contracts\Routing\NodeContents as NodeContentsContract;
use Viserio\Contracts\Routing\SegmentMatcher as SegmentMatcherContract;

class RouteTreeNode
{
    /**
     * All segment matcher.
     *
     * @var \Viserio\Contracts\Routing\SegmentMatcher[]
     */
    protected $matchers;

    /**
     * Actual route content.
     *
     * @var \Viserio\Contracts\Routing\NodeContents
     */
    protected $contents;

    /**
     * Create a new child node collection instance.
     *
     * @param \Viserio\Contracts\Routing\SegmentMatcher[] $matchers
     * @param \Viserio\Contracts\Routing\NodeContents     $contents
     */
    public function __construct(array $matchers, NodeContentsContract $contents)
    {
        if (empty($matchers)) {
            throw new RuntimeException(sprintf('Cannot construct %s: matchers must not be empty.', __CLASS__));
        }

        $this->matchers = $matchers;
        $this->contents = $contents;
    }

    /**
     * Get all matchers from array.
     *
     * @return \Viserio\Contracts\Routing\SegmentMatcher[]
     */
    public function getMatchers(): array
    {
        return $this->matchers;
    }

    /**
     * Get the first matcher from array.
     *
     * @return \Viserio\Contracts\Routing\SegmentMatcher
     */
    public function getFirstMatcher(): SegmentMatcherContract
    {
        return $this->matchers[min(array_keys($this->matchers))];
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
     * Get actual route content.
     *
     * @return \Viserio\Routing\Generator\ChildrenNodeCollection|\Viserio\Routing\Generator\MatchedRouteDataMap
     */
    public function getContents()
    {
        return $this->contents;
    }

    /**
     * Update RouteTreeNode class.
     *
     * @param array                                   $matchers
     * @param \Viserio\Contracts\Routing\NodeContents $contents
     *
     * @return \Viserio\Routing\Generator\RouteTreeNode
     */
    public function update(array $matchers, NodeContentsContract $contents): RouteTreeNode
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
