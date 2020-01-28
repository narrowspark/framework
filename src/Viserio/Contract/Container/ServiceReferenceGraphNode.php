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

use Viserio\Contract\Support\Resettable as ResettableContract;

interface ServiceReferenceGraphNode extends ResettableContract
{
    /**
     * Returns the identifier.
     *
     * @return int|string
     */
    public function getId();

    /**
     * Returns the in edges.
     *
     * @return \Viserio\Contract\Container\ServiceReferenceGraphEdge[] The in DependencyGraphEdge array
     */
    public function getInEdges(): array;

    /**
     * Returns the out edges.
     *
     * @return \Viserio\Contract\Container\ServiceReferenceGraphEdge[] The out DependencyGraphEdge array
     */
    public function getOutEdges(): array;

    /**
     * Returns the value of this Node.
     *
     * @return mixed The value
     */
    public function getValue();

    /**
     * Add a in edge instance.
     *
     * @param \Viserio\Contract\Container\ServiceReferenceGraphEdge $edge
     */
    public function addInEdge(ServiceReferenceGraphEdge $edge): void;

    /**
     * Add a out edge instance.
     *
     * @param \Viserio\Contract\Container\ServiceReferenceGraphEdge $edge
     */
    public function addOutEdge(ServiceReferenceGraphEdge $edge): void;

    /**
     * Checks if the value of this node is an Alias.
     *
     * @return bool True if the value is an Alias instance
     */
    public function isAlias(): bool;

    /**
     * Checks if the value of this node is a Definition.
     *
     * @return bool True if the value is a Definition instance
     */
    public function isDefinition(): bool;
}
