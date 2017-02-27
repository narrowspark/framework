<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Generator;

use Viserio\Component\Contracts\Routing\SegmentMatcher as SegmentMatcherContract;
use Viserio\Component\Routing\Generator\Optimizer\MatcherOptimizer;

class RouteTreeOptimizer
{
    /**
     * Optimizes the supplied route tree.
     *
     * @param array $routeTree
     *
     * @return array
     */
    public function optimize(array $routeTree): array
    {
        $segmentDepthNodeMap = $routeTree[1];

        foreach ($segmentDepthNodeMap as $segmentDepth => $nodes) {
            $segmentDepthNodeMap[$segmentDepth] = $this->optimizeNodes($nodes);
        }

        return [$routeTree[0], $segmentDepthNodeMap];
    }

    /**
     * @param \Viserio\Component\Routing\Generator\ChildrenNodeCollection $nodes
     *
     * @return \Viserio\Component\Routing\Generator\ChildrenNodeCollection
     */
    protected function optimizeNodes(ChildrenNodeCollection $nodes): ChildrenNodeCollection
    {
        $optimizedNodes = [];

        foreach ($nodes->getChildren() as $key => $node) {
            $optimizedNodes[$key] = $this->optimizeNode($node);
        }

        $optimizedNodes = new ChildrenNodeCollection($optimizedNodes);
        $optimizedNodes = $this->moveCommonMatchersToParentNode($optimizedNodes);

        return $optimizedNodes;
    }

    /**
     * [optimizeNode description].
     *
     * @param RouteTreeNode $node
     *
     * @return RouteTreeNode
     */
    protected function optimizeNode(RouteTreeNode $node): RouteTreeNode
    {
        $matchers = $node->getMatchers();
        $contents = $node->getContents();

        if ($node->isParentNode()) {
            $contents = $this->optimizeNodes($node->getContents());
            $children = $contents->getChildren();

            if (count($children) === 1) {
                $childNode = reset($children);
                $matchers  = MatcherOptimizer::mergeMatchers($node->getMatchers(), $childNode->getMatchers());
                $contents  = $childNode->getContents();
            }
        }

        $matchers = MatcherOptimizer::optimizeMatchers($matchers);

        return $node->update($matchers, $contents);
    }

    /**
     * @param \Viserio\Component\Routing\Generator\ChildrenNodeCollection $nodeCollection
     *
     * @return \Viserio\Component\Routing\Generator\ChildrenNodeCollection
     */
    protected function moveCommonMatchersToParentNode(ChildrenNodeCollection $nodeCollection): ChildrenNodeCollection
    {
        $nodes = $nodeCollection->getChildren();

        if (count($nodes) <= 1) {
            return $nodeCollection;
        }

        $children = [];
        $previous = array_shift($nodes);

        foreach ($nodes as $node) {
            $parent = $this->extractCommonParentNode($previous, $node);

            if ($parent) {
                $previous = $parent;
            } else {
                $children[] = $previous;
                $previous   = $node;
            }
        }

        $children[] = $previous;

        return new ChildrenNodeCollection($children);
    }

    /**
     * @param \Viserio\Component\Routing\Generator\RouteTreeNode $node1
     * @param \Viserio\Component\Routing\Generator\RouteTreeNode $node2
     *
     * @return \Viserio\Component\Routing\Generator\RouteTreeNode|void
     */
    protected function extractCommonParentNode(RouteTreeNode $node1, RouteTreeNode $node2)
    {
        $matcherCompare = function (SegmentMatcherContract $matcher, SegmentMatcherContract $matcher2) {
            return strcmp($matcher->getHash(), $matcher2->getHash());
        };

        $commonMatchers = array_uintersect_assoc($node1->getMatchers(), $node2->getMatchers(), $matcherCompare);

        if (empty($commonMatchers)) {
            return;
        }

        $children = [];
        $nodes    = [$node1, $node2];

        foreach ($nodes as $node) {
            $specificMatchers  = array_udiff_assoc($node->getMatchers(), $commonMatchers, $matcherCompare);
            $duplicateMatchers = array_uintersect_assoc($node->getMatchers(), $commonMatchers, $matcherCompare);

            foreach ($duplicateMatchers as $segmentDepth => $matcher) {
                $commonMatchers[$segmentDepth]->mergeParameterKeys($matcher);
            }

            if (empty($specificMatchers) && $node->isParentNode()) {
                foreach ($node->getContents()->getChildren() as $childNode) {
                    $children[] = $childNode;
                }
            } else {
                $children[] = $node->update($specificMatchers, $node->getContents());
            }
        }

        return new RouteTreeNode($commonMatchers, new ChildrenNodeCollection($children));
    }
}
