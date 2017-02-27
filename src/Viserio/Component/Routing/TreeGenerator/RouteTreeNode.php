<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\TreeGenerator;

use RuntimeException;
use Viserio\Component\Contracts\Routing\SegmentMatcher as SegmentMatcherContract;

final class RouteTreeNode
{
    /**
     * All segment matcher.
     *
     * @var \Viserio\Component\Contracts\Routing\SegmentMatcher[]
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
     * @param \Viserio\Component\Contracts\Routing\SegmentMatcher[]                                                                        $matchers
     * @param \Viserio\Component\Routing\TreeGenerator\ChildrenNodeCollection|\Viserio\Component\Routing\TreeGenerator\MatchedRouteDataMap $contents
     *
     * @throws \RuntimeException
     */
    public function __construct(array $matchers, $contents)
    {
        if (empty($matchers)) {
            throw new RuntimeException(sprintf('Cannot construct %s: matchers must not be empty.', __CLASS__));
        }

        $this->checkForNodeClass($contents);

        $this->matchers = $matchers;
        $this->contents = $contents;
    }

    /**
     * Get all matchers from array.
     *
     * @return \Viserio\Component\Contracts\Routing\SegmentMatcher[]
     */
    public function getMatchers(): array
    {
        return $this->matchers;
    }

    /**
     * Get the first matcher from array.
     *
     * @return \Viserio\Component\Contracts\Routing\SegmentMatcher
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
     * @return \Viserio\Component\Routing\TreeGenerator\ChildrenNodeCollection|\Viserio\Component\Routing\TreeGenerator\MatchedRouteDataMap
     */
    public function getContents()
    {
        return $this->contents;
    }

    /**
     * Update RouteTreeNode class.
     *
     * @param array                                             $matchers
     * @param \Viserio\Component\Routing\TreeGenerator\ChildrenNodeCollection|\Viserio\Component\Routing\TreeGenerator\MatchedRouteDataMap $contents
     *
     * @throws \RuntimeException
     *
     * @return \Viserio\Component\Routing\TreeGenerator\RouteTreeNode
     */
    public function update(array $matchers, $contents): RouteTreeNode
    {
        $this->checkForNodeClass($contents);

        if ($this->matchers === $matchers && $this->contents === $contents) {
            return $this;
        }

        $clone           = clone $this;
        $clone->matchers = $matchers;
        $clone->contents = $contents;

        return $clone;
    }

    /**
     * Check if node class a given.
     *
     * @param \Viserio\Component\Routing\TreeGenerator\ChildrenNodeCollection|\Viserio\Component\Routing\TreeGenerator\MatchedRouteDataMap $contents
     *
     * @throws \RuntimeException
     *
     * @return void
     */
    private function checkForNodeClass($contents): void
    {
        if ($contents instanceof MatchedRouteDataMap) {
            return;
        } elseif ($contents instanceof ChildrenNodeCollection) {
            return;
        }

        throw new RuntimeException(sprintf(
            'RouteTreeNode needs "Viserio\Component\Routing\TreeGenerator\ChildrenNodeCollection" or "Viserio\Component\Routing\TreeGenerator\MatchedRouteDataMap" but %s given.',
            $contents
        ));
    }
}
