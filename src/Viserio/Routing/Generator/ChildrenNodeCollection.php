<?php
declare(strict_types=1);
namespace Viserio\Routing\Generator;

use Viserio\Contracts\Routing\NodeContents as NodeContentsContract;
use Viserio\Contracts\Routing\SegmentMatcher as SegmentMatcherContract;

final class ChildrenNodeCollection implements NodeContentsContract
{
    /**
     * @var \Viserio\Contracts\Routing\RouteTreeNode[]
     */
    protected $children = [];

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
     * @return \Viserio\Contracts\Routing\RouteTreeNode[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @param \Viserio\Contracts\Routing\RouteTreeNode $node
     *
     * @return bool
     */
    public function hasChild(RouteTreeNode $node): bool
    {
        return $this->hasChildFor($node->getFirstMatcher());
    }

    /**
     * @param \Viserio\Contracts\Routing\SegmentMatcher $matcher
     *
     * @return bool
     */
    public function hasChildFor(SegmentMatcherContract $matcher): bool
    {
        $hash = $matcher->getHash();

        return isset($this->children[$hash]);
    }

    /**
     * @param \Viserio\Contracts\Routing\SegmentMatcher $matcher
     *
     * @return \Viserio\Contracts\Routing\RouteTreeNode|null
     */
    public function getChild(SegmentMatcherContract $matcher)
    {
        return $this->children[$matcher->getHash()] ?? null;
    }

    /**
     * @param \Viserio\Contracts\Routing\RouteTreeNode $node
     */
    public function addChild(RouteTreeNode $node)
    {
        $hash = $node->getFirstMatcher()->getHash();

        $this->children[$hash] = $node;
    }
}
