<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\TreeGenerator;

use Viserio\Component\Contracts\Routing\SegmentMatcher as SegmentMatcherContract;

final class ChildrenNodeCollection
{
    /**
     * All added children routes.
     *
     * @var \Viserio\Component\Routing\TreeGenerator\RouteTreeNode[]
     */
    private $children;

    /**
     * Create a new child node collection instance.
     *
     * @param array $children
     */
    public function __construct(array $children = [])
    {
        $this->children = $children;
    }

    /**
     * @return \Viserio\Component\Routing\TreeGenerator\RouteTreeNode[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @param \Viserio\Component\Routing\TreeGenerator\RouteTreeNode $node
     *
     * @return bool
     */
    public function hasChild(RouteTreeNode $node): bool
    {
        return $this->hasChildFor($node->getFirstMatcher());
    }

    /**
     * @param \Viserio\Component\Contracts\Routing\SegmentMatcher $matcher
     *
     * @return bool
     */
    public function hasChildFor(SegmentMatcherContract $matcher): bool
    {
        return isset($this->children[$matcher->getHash()]);
    }

    /**
     * @param \Viserio\Component\Contracts\Routing\SegmentMatcher $matcher
     *
     * @return null|\Viserio\Component\Routing\TreeGenerator\RouteTreeNode
     */
    public function getChild(SegmentMatcherContract $matcher): ?RouteTreeNode
    {
        return $this->children[$matcher->getHash()] ?? null;
    }

    /**
     * @param \Viserio\Component\Routing\TreeGenerator\RouteTreeNode $node
     */
    public function addChild(RouteTreeNode $node): void
    {
        $hash = $node->getFirstMatcher()->getHash();

        $this->children[$hash] = $node;
    }
}
