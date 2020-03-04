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

use Viserio\Contract\Routing\SegmentMatcher as SegmentMatcherContract;

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
     */
    public function hasChild(RouteTreeNode $node): bool
    {
        return $this->hasChildFor($node->getFirstMatcher());
    }

    public function hasChildFor(SegmentMatcherContract $matcher): bool
    {
        return isset($this->children[$matcher->getHash()]);
    }

    /**
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
