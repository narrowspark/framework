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

namespace Viserio\Contract\Container;

interface ServiceReferenceGraphEdge
{
    /**
     * Returns the source node.
     *
     * @return \Viserio\Contract\Container\ServiceReferenceGraphNode
     */
    public function getSourceNode(): ServiceReferenceGraphNode;

    /**
     * Returns the destination node.
     *
     * @return \Viserio\Contract\Container\ServiceReferenceGraphNode
     */
    public function getDestNode(): ServiceReferenceGraphNode;

    /**
     * Returns the value of the edge.
     */
    public function getValue();

    /**
     * Returns true if the edge is lazy, meaning it's a dependency not requiring direct instantiation.
     */
    public function isLazy(): bool;

    /**
     * Returns true if the edge is weak, meaning it shouldn't prevent removing the target service.
     */
    public function isWeak(): bool;

    /**
     * Returns true if the edge links with a constructor argument.
     */
    public function isReferencedByConstructor(): bool;
}
