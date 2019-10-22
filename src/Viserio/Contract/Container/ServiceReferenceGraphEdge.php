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
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Returns true if the edge is lazy, meaning it's a dependency not requiring direct instantiation.
     *
     * @return bool
     */
    public function isLazy(): bool;

    /**
     * Returns true if the edge is weak, meaning it shouldn't prevent removing the target service.
     *
     * @return bool
     */
    public function isWeak(): bool;

    /**
     * Returns true if the edge links with a constructor argument.
     *
     * @return bool
     */
    public function isReferencedByConstructor(): bool;
}
